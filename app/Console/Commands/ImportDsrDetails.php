<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportDsrDetails extends Command
{
    protected $signature = 'import:dsr-details {--start-id=0 : Starting ID for import} {--debug : Enable debug mode}';
    protected $description = 'Import acm_dsr_details from old_crm_db into laravelcrm.dsrs starting from a specific ID';

    public function handle()
    {
        $startTime = microtime(true);
        $startId = (int) $this->option('start-id');
        $debugMode = $this->option('debug');
        $this->info("Starting import of acm_dsr_details from ID > {$startId}... Debug mode: " . ($debugMode ? 'ON' : 'OFF'));
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
        $requiredTables = ['users', 'projects', 'dsrs'];
        foreach ($requiredTables as $table) {
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable($table)) {
                $this->error("$table table does not exist in laravelcrm.");
                Log::error("$table table not found");
                return 1;
            }
        }

        // Create skipped_dsrs table if it doesn't exist
        if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('skipped_dsrs')) {
            DB::connection('mysql')->getSchemaBuilder()->create('skipped_dsrs', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('dsr_id')->nullable();
                $table->string('error_message', 255);
                $table->timestamp('created_at')->useCurrent();
            });
            $this->info('Created skipped_dsrs table.');
        }

        // Cache valid IDs
        $this->info('Caching valid user and project IDs...');
        $validUserIds = DB::table('users')->pluck('id')->toArray();
        $validProjectIds = DB::table('projects')->pluck('id')->toArray();
        $this->info('Found ' . count($validUserIds) . ' valid users and ' . count($validProjectIds) . ' valid projects.');

        // Create placeholder project
        DB::table('projects')->upsert(
            [[
                'id' => 99999,
                'sale_team_project_id' => 99999,
                'name_or_url' => 'Placeholder Project',
                'project_status' => 'Working',
                'created_at' => now(),
                'updated_at' => now(),
                'project_manager_id' => 22369,
                'assign_main_employee_id' => 22369,
                'sales_person_id' => 22369,
                'created_by' => 22369, // Added to fix error
                'department_id' => DB::table('departments')->min('id') ?? 1,
                'project_category_id' => DB::table('project_categories')->min('id') ?? null,
                'client_type' => 'Old Client',
                'report_type' => 'Weekly',
                'project_type' => 'One-time',
                'business_type' => 'Enterprise',
                'can_client_rehire' => 'No',
                'content_details' => json_encode([
                    'price' => 0,
                    'type' => null,
                    'quantity' => null,
                    'specific_keywords' => '',
                    'specific_commitment' => '',
                    'content_commitment' => '',
                    'websitework_commitment' => '',
                ]),
            ]],
            ['id'],
            ['name_or_url', 'project_status', 'updated_at']
        );
        $validProjectIds[] = 99999;

        // Cache user mappings
        $this->info('Building user ID mappings...');
        $userMappingByEmail = DB::connection('old_crm_db')
            ->table('acm_users')
            ->select('id', 'email')
            ->whereIn('id', DB::connection('old_crm_db')->table('acm_dsr_details')->selectRaw('DISTINCT user_id')
                ->union(DB::connection('old_crm_db')->table('acm_dsr_details')->selectRaw('DISTINCT help_user')))
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
            ->whereIn('id', DB::connection('old_crm_db')->table('acm_dsr_details')->selectRaw('DISTINCT user_id')
                ->union(DB::connection('old_crm_db')->table('acm_dsr_details')->selectRaw('DISTINCT help_user')))
            ->get()
            ->mapWithKeys(function ($user) use ($debugMode) {
                $newId = DB::table('users')->whereRaw('LOWER(name) = ?', [strtolower($user->name)])->value('id');
                if ($debugMode && !$newId) {
                    $this->warn("No name match for old user_id {$user->id} (name: {$user->name})");
                }
                return [$user->id => $newId];
            })->filter()->toArray();

        $this->info('Mapped ' . count($userMappingByEmail) . ' users by email and ' . count($userMappingByName) . ' users by name.');

        // Process records in chunks
        $this->info("Processing records from ID > {$startId}...");
        $chunkResult = DB::connection('old_crm_db')
            ->table('acm_dsr_details')
            ->where('id', '>', $startId)
            ->orderBy('id')
            ->chunk(5000, function ($records) use (&$inserted, &$failed, &$skipped, &$processed, $validUserIds, $validProjectIds, $userMappingByEmail, $userMappingByName, $startTime, $debugMode) {
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
                        $this->info("Processing Record ID {$record->id}: user_id={$record->user_id}, project_id={$record->project_id}, help_user={$record->help_user}");
                    }

                    // Map user_id and help_user
                    $newUserId = $userMappingByEmail[$record->user_id] ?? $userMappingByName[$record->user_id] ?? null;
                    $newHelpUserId = $record->help_user ? ($userMappingByEmail[$record->help_user] ?? $userMappingByName[$record->help_user] ?? null) : null;
                    $newProjectId = in_array($record->project_id, $validProjectIds) ? $record->project_id : 99999;

                    // Validate foreign keys
                    if (!$newUserId || !in_array($newUserId, $validUserIds)) {
                        $reason = "Invalid user_id ({$record->user_id}) - no matching email or name in laravelcrm.users";
                        $skipped[] = "Record ID {$record->id}: $reason";
                        DB::table('skipped_dsrs')->insert(['dsr_id' => $record->id, 'error_message' => $reason, 'created_at' => now()]);
                        Log::warning("Skipped Record ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }
                    if ($record->help_user && !$newHelpUserId) {
                        $reason = "Invalid help_user ({$record->help_user}) - no matching email or name in laravelcrm.users";
                        $skipped[] = "Record ID {$record->id}: $reason";
                        DB::table('skipped_dsrs')->insert(['dsr_id' => $record->id, 'error_message' => $reason, 'created_at' => now()]);
                        Log::warning("Skipped Record ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }

                    $batch[] = [
                        'id' => $record->id,
                        'user_id' => $newUserId,
                        'project_id' => $newProjectId,
                        'work_description' => $record->work_desc ?? '',
                        'hours' => floor($record->hours ?? 0),
                        'helped_by' => $newHelpUserId,
                        'help_description' => $record->someone_helped && $record->someone_helped !== '?' ? $record->someone_helped : null,
                        'help_rating' => is_numeric($record->help_rating) && $record->help_rating !== '?' ? $record->help_rating : null,
                        'replied_to_emails' => 0,
                        'sent_report' => 0,
                        'justified_work' => 0,
                        'created_at' => $record->dsr_date ? date('Y-m-d H:i:s', strtotime($record->dsr_date)) : ($record->created_at ?: now()),
                        'updated_at' => $record->viewed_at ? date('Y-m-d H:i:s', strtotime($record->viewed_at)) : now(),
                    ];
                }

                if (!empty($batch)) {
                    try {
                        DB::table('dsrs')->upsert(
                            $batch,
                            ['id'],
                            ['work_description', 'hours', 'helped_by', 'help_description', 'help_rating', 'project_id', 'user_id', 'updated_at']
                        );
                        $inserted += count($batch);
                        if ($debugMode) {
                            $this->info("Inserted/Updated " . count($batch) . " records");
                        }
                    } catch (\Exception $e) {
                        $reason = "Batch upsert failed: {$e->getMessage()}";
                        $skipped[] = "Batch starting at ID {$batch[0]['id']}: $reason";
                        DB::table('skipped_dsrs')->insert(['dsr_id' => $batch[0]['id'], 'error_message' => $reason, 'created_at' => now()]);
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
            Log::warning('Skipped records during DSR import:', array_slice($skipped, 0, 100));
            $this->warn("Skipped records:\n" . implode("\n", array_slice($skipped, 0, 100)) . (count($skipped) > 100 ? "\n...and " . (count($skipped) - 100) . " more" : ""));
        }

        $elapsed = microtime(true) - $startTime;
        $this->info("Import completed. Inserted: $inserted, Failed: $failed, Total Time: " . round($elapsed / 60, 2) . " minutes");
        return 0;
    }
}