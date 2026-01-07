<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportTaskPhases extends Command
{
    protected $signature = 'import:task-phases {--start-id=0 : Starting ID for import} {--debug : Enable debug mode}';
    protected $description = 'Import acm_task_phases from old_crm_db into laravelcrm.task_phases starting from a specific ID';

    public function handle()
    {
        $startTime = microtime(true);
        $startId = (int) $this->option('start-id');
        $debugMode = $this->option('debug');
        $this->info("Starting import of acm_task_phases from ID > {$startId}... Debug mode: " . ($debugMode ? 'ON' : 'OFF'));

        $inserted = 0;
        $failed = 0;
        $skipped = [];
        $processed = 0;

        // ✅ 1. Verify database connections
        try {
            DB::connection('old_crm_db')->getPdo();
            $this->info('✅ Connected to old_crm_db');
            $database = DB::connection('mysql')->getDatabaseName();
            DB::connection('mysql')->getPdo();
            $this->info("✅ Connected to laravelcrm (database: $database)");
        } catch (\Exception $e) {
            $this->error("❌ Database connection failed: {$e->getMessage()}");
            Log::error("Database connection failed: {$e->getMessage()}");
            return 1;
        }

        // ✅ 2. Verify required tables
        $requiredTables = ['users', 'task_phases'];
        foreach ($requiredTables as $table) {
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable($table)) {
                $this->error("❌ $table table does not exist in laravelcrm.");
                Log::error("$table table not found");
                return 1;
            }
        }

        // ✅ 3. Cache valid user IDs
        $this->info('Caching valid user IDs...');
        $validUserIds = DB::table('users')->pluck('id')->toArray();
        $this->info('Found ' . count($validUserIds) . ' valid users.');

        // ✅ 4. Build user ID mapping (old → new)
        $this->info('Building user ID mappings...');
        $userMapping = DB::connection('old_crm_db')
            ->table('acm_users')
            ->select('id', 'email', 'name')
            ->get()
            ->mapWithKeys(function ($user) use ($debugMode) {
                $newUserId = DB::table('users')
                    ->whereRaw('LOWER(email) = ?', [strtolower($user->email)])
                    ->orWhereRaw('LOWER(name) = ?', [strtolower($user->name)])
                    ->value('id');
                if ($debugMode && !$newUserId) {
                    $this->warn("⚠️ No match for old user_id {$user->id} ({$user->email} / {$user->name})");
                }
                return [$user->id => $newUserId];
            })->filter()->toArray();

        $this->info('Mapped ' . count($userMapping) . ' users.');

        // ✅ 5. Process old task phases in chunks
        $this->info("Processing records from ID > {$startId}...");
        $chunkResult = DB::connection('old_crm_db')
            ->table('acm_task_phases')
            ->where('id', '>', $startId)
            ->where('isDeleted', 'no')
            ->orderBy('id')
            ->chunk(2000, function ($records) use (&$inserted, &$failed, &$skipped, &$processed, $validUserIds, $userMapping, $startTime, $debugMode) {

                $batch = [];
                foreach ($records as $record) {
                    $processed++;

                    // ✅ Log progress every 5000 records
                    if ($processed % 5000 == 0) {
                        $elapsed = microtime(true) - $startTime;
                        $this->info("Processed $processed records... Inserted: $inserted, Failed: $failed, Elapsed: " . round($elapsed / 60, 2) . " min");
                    }

                    if ($debugMode) {
                        $this->info("Processing Phase ID {$record->id}: {$record->title}");
                    }

                    // ❌ Skip empty title
                    if (empty(trim($record->title))) {
                        $reason = "Empty title for ID {$record->id}";
                        $skipped[] = $reason;
                        Log::warning("Skipped Phase ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }

                    // ✅ Map created_by
                    $newUserId = $userMapping[$record->created_by_user_id] ?? null;
                    if ($newUserId && !in_array($newUserId, $validUserIds)) {
                        $newUserId = null;
                        if ($debugMode) {
                            $this->warn("Invalid mapped user for Phase ID {$record->id}, setting created_by=null");
                        }
                    }

                    // ✅ Handle timestamps safely
                    $createdAt = $this->normalizeDate($record->created_at);
                    $updatedAt = $this->normalizeDate($record->updated_at);

                    // ✅ Build record array
                    $batch[] = [
                        'id'          => (int) $record->id,
                        'title'       => substr($record->title, 0, 255),
                        'description' => $record->description ?? null,
                        'created_by'  => $newUserId,
                        'created_at'  => $createdAt,
                        'updated_at'  => $updatedAt,
                    ];
                }

                // ✅ Insert or update batch
                if (!empty($batch)) {
                    try {
                        DB::table('task_phases')->upsert(
                            $batch,
                            ['id'],
                            ['title', 'description', 'created_by', 'created_at', 'updated_at']
                        );
                        $inserted += count($batch);
                        if ($debugMode) {
                            $this->info("Inserted/Updated " . count($batch) . " task phases");
                        }
                    } catch (\Exception $e) {
                        $reason = "Batch insert failed: {$e->getMessage()}";
                        $skipped[] = $reason;
                        Log::error($reason);
                        $failed += count($batch);
                    }
                }
            });

        if ($chunkResult === false) {
            $this->error("Import interrupted. Check logs for details.");
            Log::error("Import interrupted at ID > {$startId}");
        }

        // ✅ 6. Summary
        $elapsed = microtime(true) - $startTime;
        $this->info("🎉 Import completed. Inserted: $inserted, Failed: $failed, Total Time: " . round($elapsed / 60, 2) . " min");

        if (!empty($skipped)) {
            $this->warn("⚠️ Skipped Records (" . count($skipped) . "):\n" . implode("\n", array_slice($skipped, 0, 50)));
        }

        Log::info("Task Phases Import Summary", [
            'inserted' => $inserted,
            'failed' => $failed,
            'duration_sec' => round($elapsed, 2)
        ]);

        return 0;
    }

    /**
     * Normalize date or fallback to now()
     */
    protected function normalizeDate($value)
    {
        if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return now();
        }
        try {
            return (new \DateTime($value))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::warning("Invalid date value '{$value}', fallback to now()");
            return now();
        }
    }
}
