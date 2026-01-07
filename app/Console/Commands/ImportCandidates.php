<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportCandidates extends Command
{
    protected $signature = 'import:candidates {--start-id=0 : Starting ID for import} {--debug : Enable debug mode}';
    protected $description = 'Import acm_candidates from old_crm_db into laravelcrm.candidates starting from a specific ID';

    public function handle()
    {
        $startTime = microtime(true);
        $startId = (int) $this->option('start-id');
        $debugMode = $this->option('debug');
        $this->info("Starting import of acm_candidates from ID > {$startId}... Debug mode: " . ($debugMode ? 'ON' : 'OFF'));
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
        $requiredTables = ['users', 'departments', 'candidates'];
        foreach ($requiredTables as $table) {
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable($table)) {
                $this->error("$table table does not exist in laravelcrm.");
                Log::error("$table table not found");
                return 1;
            }
        }

        // Create skipped_candidates table if it doesn't exist
        if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('skipped_candidates')) {
            DB::connection('mysql')->getSchemaBuilder()->create('skipped_candidates', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('candidate_id')->nullable();
                $table->string('error_message', 255);
                $table->timestamp('created_at')->useCurrent();
            });
            $this->info('Created skipped_candidates table.');
        }

        // Cache valid IDs
        $this->info('Caching valid user and department IDs...');
        $validUserIds = DB::table('users')->pluck('id')->toArray();
        $validDepartmentIds = DB::table('departments')->pluck('id')->toArray();
        $defaultDepartmentId = !empty($validDepartmentIds) ? min($validDepartmentIds) : null;
        $this->info('Found ' . count($validUserIds) . ' valid users, ' . count($validDepartmentIds) . ' valid departments.');

        // Cache user mappings
        $this->info('Building user ID mappings...');
        $userMappingByEmail = DB::connection('old_crm_db')
            ->table('acm_users')
            ->select('id', 'email')
            ->whereIn('id', DB::connection('old_crm_db')->table('acm_candidates')->selectRaw('DISTINCT created_by_user_id'))
            ->get()
            ->mapWithKeys(function ($user) use ($debugMode) {
                $newId = DB::table('users')->whereRaw('LOWER(email) = ?', [strtolower($user->email)])->value('id');
                if ($debugMode && !$newId) {
                    $this->warn("No email match for old user_id {$user->id} (email: {$user->email})");
                }
                return [$user->id => $newId];
            })->filter()->toArray();

        $userMappingByName = DB::connection('old_crm_db')
            ->table('acm_users')
            ->select('id', 'name')
            ->whereIn('id', DB::connection('old_crm_db')->table('acm_candidates')->selectRaw('DISTINCT created_by_user_id'))
            ->get()
            ->mapWithKeys(function ($user) use ($debugMode) {
                $newId = DB::table('users')->whereRaw('LOWER(name) = ?', [strtolower($user->name)])->value('id');
                if ($debugMode && !$newId) {
                    $this->warn("No name match for old user_id {$user->id} (name: {$user->name})");
                }
                return [$user->id => $newId];
            })->filter()->toArray();

        $this->info('Mapped ' . count($userMappingByEmail) . ' users by email and ' . count($userMappingByName) . ' users by name.');

        // Valid status values in target
        $validStatuses = ['Shortlist', 'Scheduled', 'Offered', 'Hired'];
        $defaultStatus = 'Shortlist';

        // Process records in chunks
        $this->info("Processing records from ID > {$startId}...");
        $chunkResult = DB::connection('old_crm_db')
            ->table('acm_candidates')
            ->where('id', '>', $startId)
            ->where('is_deleted', 'no')
            ->orderBy('id')
            ->chunk(5000, function ($records) use (&$inserted, &$failed, &$skipped, &$processed, $validUserIds, $validDepartmentIds, $defaultDepartmentId, $userMappingByEmail, $userMappingByName, $validStatuses, $defaultStatus, $startTime, $debugMode) {
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
                        $this->info("Processing Record ID {$record->id}: created_by_user_id={$record->created_by_user_id}, department_id={$record->department_id}, status={$record->status}, email={$record->email}");
                    }

                    // Map user ID
                    $newUserId = $userMappingByEmail[$record->created_by_user_id] ?? $userMappingByName[$record->created_by_user_id] ?? null;

                    // Validate foreign keys
                    if (!$newUserId || !in_array($newUserId, $validUserIds)) {
                        $reason = "Invalid created_by_user_id ({$record->created_by_user_id}) - no matching email or name in laravelcrm.users";
                        $skipped[] = "Record ID {$record->id}: $reason";
                        try {
                            DB::table('skipped_candidates')->insert([
                                'candidate_id' => (int) $record->id,
                                'error_message' => substr($reason, 0, 255),
                                'created_at' => now()
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Failed to insert into skipped_candidates for ID {$record->id}: {$e->getMessage()}");
                        }
                        Log::warning("Skipped Record ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }

                    // Validate department_id
                    $newDepartmentId = $record->department_id && in_array($record->department_id, $validDepartmentIds) ? $record->department_id : $defaultDepartmentId;
                    if (!$newDepartmentId) {
                        $reason = "Invalid or missing department_id ({$record->department_id}) - no valid departments available";
                        $skipped[] = "Record ID {$record->id}: $reason";
                        try {
                            DB::table('skipped_candidates')->insert([
                                'candidate_id' => (int) $record->id,
                                'error_message' => substr($reason, 0, 255),
                                'created_at' => now()
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Failed to insert into skipped_candidates for ID {$record->id}: {$e->getMessage()}");
                        }
                        Log::warning("Skipped Record ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }

                    // Map status
                    $status = in_array($record->status, $validStatuses) ? $record->status : $defaultStatus;

                    // Handle invalid date_of_joining
                    $dateOfJoining = $record->date_of_joining && $record->date_of_joining !== '0000-00-00' ? $record->date_of_joining : null;

                    // Clean salary and experience fields
                    $currentSalary = $record->current_salary ? substr(trim(preg_replace('/[^0-9.k-]/', '', $record->current_salary)), 0, 255) : null;
                    $expectedSalary = $record->expected_salary ? substr(trim(preg_replace('/[^0-9.k-]/', '', $record->expected_salary)), 0, 255) : null;
                    $offeredSalary = $record->offered_salary ? substr(trim(preg_replace('/[^0-9.k-]/', '', $record->offered_salary)), 0, 255) : null;
                    $experience = $record->experience ? substr(trim(preg_replace('/[^0-9a-zA-Z\s.-]/', '', $record->experience)), 0, 255) : null;

                    $batch[] = [
                        'id' => (int) $record->id,
                        'name' => substr($record->name, 0, 255),
                        'email' => substr($record->email, 0, 255),
                        'phone_number' => substr($record->phone_no, 0, 255),
                        'experience' => $experience,
                        'current_salary' => $currentSalary,
                        'expected_salary' => $expectedSalary,
                        'offered_salary' => $offeredSalary,
                        'date_of_joining' => $dateOfJoining,
                        'comments' => $record->comments,
                        'resume' => $record->resume_name ? substr($record->resume_name, 0, 255) : null,
                        'department_id' => (int) $newDepartmentId,
                        'status' => $status,
                        'created_at' => $record->created_at ?: now(),
                        'updated_at' => $record->updated_at ?: now(),
                        'added_by' => $newUserId ? (int) $newUserId : null,
                    ];
                }

                if (!empty($batch)) {
                    try {
                        DB::table('candidates')->upsert(
                            $batch,
                            ['id'],
                            ['name', 'email', 'phone_number', 'experience', 'current_salary', 'expected_salary', 'offered_salary', 'date_of_joining', 'comments', 'resume', 'department_id', 'status', 'created_at', 'updated_at', 'added_by']
                        );
                        $inserted += count($batch);
                        if ($debugMode) {
                            $this->info("Inserted/Updated " . count($batch) . " records");
                        }
                    } catch (\Exception $e) {
                        $reason = "Batch upsert failed: {$e->getMessage()}";
                        $skipped[] = "Batch starting at ID {$batch[0]['id']}: $reason";
                        try {
                            DB::table('skipped_candidates')->insert([
                                'candidate_id' => (int) $batch[0]['id'],
                                'error_message' => substr($reason, 0, 255),
                                'created_at' => now()
                            ]);
                        } catch (\Exception $e2) {
                            Log::error("Failed to insert into skipped_candidates for batch ID {$batch[0]['id']}: {$e2->getMessage()}");
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
            Log::warning('Skipped records during candidates import:', array_slice($skipped, 0, 100));
            $this->warn("Skipped records:\n" . implode("\n", array_slice($skipped, 0, 100)) . (count($skipped) > 100 ? "\n...and " . (count($skipped) - 100) . " more" : ""));
        }

        $elapsed = microtime(true) - $startTime;
        $this->info("Import completed. Inserted: $inserted, Failed: $failed, Total Time: " . round($elapsed / 60, 2) . " minutes");
        return 0;
    }
}