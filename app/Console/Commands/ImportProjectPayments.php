<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportProjectPayments extends Command
{
    protected $signature = 'import:project-payments {--start-id=0 : Starting ID for import} {--debug : Enable debug mode}';
    protected $description = 'Import acm_projects_payment from old_crm_db into laravelcrm.project_payments starting from a specific ID';

    public function handle()
    {
        $startTime = microtime(true);
        $startId = (int) $this->option('start-id');
        $debugMode = $this->option('debug');
        $this->info("Starting import of acm_projects_payment from ID > {$startId}... Debug mode: " . ($debugMode ? 'ON' : 'OFF'));
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
        $requiredTables = ['users', 'projects', 'payment_accounts', 'project_payments'];
        foreach ($requiredTables as $table) {
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable($table)) {
                $this->error("$table table does not exist in laravelcrm.");
                Log::error("$table table not found");
                return 1;
            }
        }

        // Create skipped_project_payments table if it doesn't exist
        if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('skipped_project_payments')) {
            DB::connection('mysql')->getSchemaBuilder()->create('skipped_project_payments', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('project_payment_id')->nullable();
                $table->string('error_message', 255);
                $table->timestamp('created_at')->useCurrent();
            });
            $this->info('Created skipped_project_payments table.');
        }

        // Cache valid IDs
        $this->info('Caching valid user, project, and payment account IDs...');
        $validUserIds = DB::table('users')->pluck('id')->toArray();
        $validProjectIds = DB::table('projects')->pluck('id')->toArray();
        $validAccountIds = DB::table('payment_accounts')->pluck('id')->toArray();
        $this->info('Found ' . count($validUserIds) . ' valid users, ' . count($validProjectIds) . ' valid projects, and ' . count($validAccountIds) . ' valid payment accounts.');

        // Cache user mappings
        $this->info('Building user ID mappings...');
        $userMappingByEmail = DB::connection('old_crm_db')
            ->table('acm_users')
            ->select('id', 'email')
            ->whereIn('id', DB::connection('old_crm_db')->table('acm_projects_payment')->selectRaw('DISTINCT created_by_user_id'))
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
            ->whereIn('id', DB::connection('old_crm_db')->table('acm_projects_payment')->selectRaw('DISTINCT created_by_user_id'))
            ->get()
            ->mapWithKeys(function ($user) use ($debugMode) {
                $newId = DB::table('users')->whereRaw('LOWER(name) = ?', [strtolower($user->name)])->value('id');
                if ($debugMode && !$newId) {
                    $this->warn("No name match for old user_id {$user->id} (name: {$user->name})");
                }
                return [$user->id => $newId];
            })->filter()->toArray();

        $this->info('Mapped ' . count($userMappingByEmail) . ' users by email and ' . count($userMappingByName) . ' users by name.');

        // Cache account mappings
        $this->info('Building payment account ID mappings...');
        $accountMapping = DB::connection('old_crm_db')
            ->table('acm_payment_accounts')
            ->select('id', 'payment_accounts_name')
            ->whereIn('id', DB::connection('old_crm_db')->table('acm_projects_payment')->selectRaw('DISTINCT account_id'))
            ->get()
            ->mapWithKeys(function ($account) use ($debugMode) {
                $newId = DB::table('payment_accounts')->where('account_name', $account->payment_accounts_name)->value('id');
                if ($debugMode && !$newId) {
                    $this->warn("No match for old account_id {$account->id} (name: {$account->payment_accounts_name})");
                }
                return [$account->id => $newId];
            })->filter()->toArray();

        $this->info('Mapped ' . count($accountMapping) . ' payment accounts.');

        // Process records in chunks
        $this->info("Processing records from ID > {$startId}...");
        $chunkResult = DB::connection('old_crm_db')
            ->table('acm_projects_payment')
            ->where('id', '>', $startId)
            ->orderBy('id')
            ->chunk(5000, function ($records) use (&$inserted, &$failed, &$skipped, &$processed, $validUserIds, $validProjectIds, $validAccountIds, $userMappingByEmail, $userMappingByName, $accountMapping, $startTime, $debugMode) {
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
                        $this->info("Processing Record ID {$record->id}: project_id={$record->project_id}, account_id={$record->account_id}, created_by_user_id={$record->created_by_user_id}, amount={$record->amount}, amount_commision={$record->amount_commision}");
                    }

                    // Map IDs
                    $newUserId = $userMappingByEmail[$record->created_by_user_id] ?? $userMappingByName[$record->created_by_user_id] ?? null;
                    $newProjectId = in_array($record->project_id, $validProjectIds) ? $record->project_id : null;
                    $newAccountId = $accountMapping[$record->account_id] ?? null;

                    // Validate foreign keys
                    if (!$newUserId || !in_array($newUserId, $validUserIds)) {
                        $reason = "Invalid created_by_user_id ({$record->created_by_user_id}) - no matching email or name in laravelcrm.users";
                        $skipped[] = "Record ID {$record->id}: $reason";
                        DB::table('skipped_project_payments')->insert(['project_payment_id' => $record->id, 'error_message' => $reason, 'created_at' => now()]);
                        Log::warning("Skipped Record ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }
                    if (!$newProjectId) {
                        $reason = "Invalid project_id ({$record->project_id}) - not found in laravelcrm.projects";
                        $skipped[] = "Record ID {$record->id}: $reason";
                        DB::table('skipped_project_payments')->insert(['project_payment_id' => $record->id, 'error_message' => $reason, 'created_at' => now()]);
                        Log::warning("Skipped Record ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }
                    if ($record->account_id && !$newAccountId) {
                        $reason = "Invalid account_id ({$record->account_id}) - not found in laravelcrm.payment_accounts";
                        $skipped[] = "Record ID {$record->id}: $reason";
                        DB::table('skipped_project_payments')->insert(['project_payment_id' => $record->id, 'error_message' => $reason, 'created_at' => now()]);
                        Log::warning("Skipped Record ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }

                    // Use raw amounts as integers
                    $paymentAmount = $record->amount !== null ? (int) $record->amount : 0;
                    $commissionAmount = $record->amount_commision !== null && $record->amount_commision != 0 ? (int) $record->amount_commision : null;

                    $batch[] = [
                        'id' => $record->id,
                        'project_id' => $newProjectId,
                        'account_id' => $newAccountId,
                        'payment_amount' => $paymentAmount,
                        'commission_amount' => $commissionAmount,
                        'payment_month' => $record->payment_for_month,
                        'payment_details' => $record->payment_description,
                        'screenshot' => $record->payment_screenshot,
                        'created_by' => $newUserId,
                        'created_at' => $record->created_at ?: now(),
                        'updated_at' => $record->updated_at ?: now(),
                    ];
                }

                if (!empty($batch)) {
                    try {
                        DB::table('project_payments')->upsert(
                            $batch,
                            ['id'],
                            ['project_id', 'account_id', 'payment_amount', 'commission_amount', 'payment_month', 'payment_details', 'screenshot', 'created_by', 'updated_at']
                        );
                        $inserted += count($batch);
                        if ($debugMode) {
                            $this->info("Inserted/Updated " . count($batch) . " records");
                        }
                    } catch (\Exception $e) {
                        $reason = "Batch upsert failed: {$e->getMessage()}";
                        $skipped[] = "Batch starting at ID {$batch[0]['id']}: $reason";
                        DB::table('skipped_project_payments')->insert(['project_payment_id' => $batch[0]['id'], 'error_message' => $reason, 'created_at' => now()]);
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
            Log::warning('Skipped records during project payments import:', array_slice($skipped, 0, 100));
            $this->warn("Skipped records:\n" . implode("\n", array_slice($skipped, 0, 100)) . (count($skipped) > 100 ? "\n...and " . (count($skipped) - 100) . " more" : ""));
        }

        $elapsed = microtime(true) - $startTime;
        $this->info("Import completed. Inserted: $inserted, Failed: $failed, Total Time: " . round($elapsed / 60, 2) . " minutes");
        return 0;
    }
}