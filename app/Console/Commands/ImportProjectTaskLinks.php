<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportProjectTaskLinks extends Command
{
    protected $signature = 'import:project-task-links {--start-id=0 : Starting ID for import} {--debug : Enable debug mode}';
    protected $description = 'Import acm_project_task_links from old_crm_db into laravelcrm.manage_links starting from a specific ID';

    public function handle()
    {
        $startTime = microtime(true);
        $startId = (int) $this->option('start-id');
        $debugMode = $this->option('debug');
        $this->info("Starting import of acm_project_task_links from ID > {$startId}... Debug mode: " . ($debugMode ? 'ON' : 'OFF'));
        $inserted = 0;
        $failed = 0;
        $skipped = [];
        $processed = 0;

        // Verify database connections
        try {
            DB::connection('old_crm_db')->getPdo();
            $this->info('✅ Connected to old_crm_db');
            $database = DB::connection('mysql')->getDatabaseName();
            DB::connection('mysql')->getPdo();
            $this->info("✅ Connected to laravelcrm (database: $database)");
        } catch (\Exception $e) {
            $this->error("Database connection failed: {$e->getMessage()}");
            Log::error("Database connection failed: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            return 1;
        }

        // Check table existence
        $requiredTables = ['project_tasks', 'manage_links'];
        foreach ($requiredTables as $table) {
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable($table)) {
                $this->error("$table table does not exist in laravelcrm.");
                Log::error("$table table not found");
                return 1;
            }
        }

        // Create skipped_manage_links table if it doesn't exist
        if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('skipped_manage_links')) {
            DB::connection('mysql')->getSchemaBuilder()->create('skipped_manage_links', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('link_id')->nullable();
                $table->string('error_message', 255);
                $table->timestamp('created_at')->useCurrent();
            });
            $this->info('Created skipped_manage_links table.');
        }

        // Cache valid project task IDs
        $this->info('Caching valid project task IDs...');
        $validProjectTaskIds = DB::table('project_tasks')->pluck('id')->toArray();
        $this->info('Found ' . count($validProjectTaskIds) . ' valid project tasks.');

        // Process records in chunks
        $this->info("Processing records from ID > {$startId}...");
        $chunkResult = DB::connection('old_crm_db')
            ->table('acm_project_task_links')
            ->where('id', '>', $startId)
            ->where('isDeleted', 'no')
            ->orderBy('id')
            ->chunk(5000, function ($records) use (&$inserted, &$failed, &$skipped, &$processed, $validProjectTaskIds, $startTime, $debugMode) {
                $batch = [];
                foreach ($records as $record) {
                    $processed++;
                    if ($processed % 10000 == 0) {
                        $elapsed = microtime(true) - $startTime;
                        $this->info("Processed $processed records... Inserted: $inserted, Failed: $failed, Elapsed: " . round($elapsed / 60, 2) . " minutes");
                        if ($elapsed > 900) {
                            Log::error("Import timeout after processing $processed records at ID {$record->id}.");
                            $this->error("Import timed out after 15 minutes at ID {$record->id}.");
                            return false;
                        }
                    }

                    if ($debugMode) {
                        $this->info("Processing Record ID {$record->id}: project_task_id={$record->project_task_id}, url={$record->url}, pa={$record->pa}, da={$record->da}");
                    }

                    // Validate project_task_id
                    if (!in_array($record->project_task_id, $validProjectTaskIds)) {
                        $reason = "Invalid project_task_id ({$record->project_task_id}) for ID {$record->id}";
                        $skipped[] = "Record ID {$record->id}: $reason";
                        try {
                            DB::table('skipped_manage_links')->insert([
                                'link_id' => (int) $record->id,
                                'error_message' => substr($reason, 0, 255),
                                'created_at' => now()
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Failed to insert into skipped_manage_links for ID {$record->id}: {$e->getMessage()}");
                        }
                        Log::warning("Skipped Record ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }

                    // Validate url
                    if (empty($record->url)) {
                        $reason = "Empty url for ID {$record->id}";
                        $skipped[] = "Record ID {$record->id}: $reason";
                        try {
                            DB::table('skipped_manage_links')->insert([
                                'link_id' => (int) $record->id,
                                'error_message' => substr($reason, 0, 255),
                                'created_at' => now()
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Failed to insert into skipped_manage_links for ID {$record->id}: {$e->getMessage()}");
                        }
                        Log::warning("Skipped Record ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }

                    // Convert pa and da to integers
                    $pa = (int) preg_replace('/[^0-9]/', '', $record->pa);
                    $da = $record->da ? (int) preg_replace('/[^0-9]/', '', $record->da) : 0;

                    // Validate pa and da
                    if ($pa === 0 && !empty($record->pa)) {
                        $reason = "Invalid pa value ({$record->pa}) for ID {$record->id}";
                        $skipped[] = "Record ID {$record->id}: $reason";
                        try {
                            DB::table('skipped_manage_links')->insert([
                                'link_id' => (int) $record->id,
                                'error_message' => substr($reason, 0, 255),
                                'created_at' => now()
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Failed to insert into skipped_manage_links for ID {$record->id}: {$e->getMessage()}");
                        }
                        Log::warning("Skipped Record ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }

                    $batch[] = [
                        'id' => (int) $record->id,
                        'project_task_id' => (int) $record->project_task_id,
                        'link' => substr($record->url, 0, 255),
                        'pa' => $pa,
                        'da' => $da,
                        'created_at' => $record->created_at ?: now(),
                        'updated_at' => $record->updated_at ?: now(),
                    ];
                }

                if (!empty($batch)) {
                    try {
                        DB::table('manage_links')->upsert(
                            $batch,
                            ['id'],
                            ['project_task_id', 'link', 'pa', 'da', 'created_at', 'updated_at']
                        );
                        $inserted += count($batch);
                        if ($debugMode) {
                            $this->info("Inserted/Updated " . count($batch) . " records");
                        }
                    } catch (\Exception $e) {
                        $reason = "Batch upsert failed: {$e->getMessage()}";
                        $skipped[] = "Batch starting at ID {$batch[0]['id']}: $reason";
                        try {
                            DB::table('skipped_manage_links')->insert([
                                'link_id' => (int) $batch[0]['id'],
                                'error_message' => substr($reason, 0, 255),
                                'created_at' => now()
                            ]);
                        } catch (\Exception $e2) {
                            Log::error("Failed to insert into skipped_manage_links for batch ID {$batch[0]['id']}: {$e2->getMessage()}");
                        }
                        Log::error($reason, ['batch_size' => count($batch)]);
                        $failed += count($batch);
                    }
                }
            });

        if ($chunkResult === false) {
            $this->error("Import interrupted. Check logs for details.");
            Log::error("Import interrupted at ID > {$startId}");
        }

        Log::info("Import completed. Inserted: $inserted, Failed: $failed");
        if (!empty($skipped)) {
            Log::warning('Skipped records during manage_links import:', array_slice($skipped, 0, 100));
            $this->warn("Skipped records:\n" . implode("\n", array_slice($skipped, 0, 100)) . (count($skipped) > 100 ? "\n...and " . (count($skipped) - 100) . " more" : ""));
        }

        $elapsed = microtime(true) - $startTime;
        $this->info("Import completed. Inserted: $inserted, Failed: $failed, Total Time: " . round($elapsed / 60, 2) . " minutes");
        return 0;
    }
}