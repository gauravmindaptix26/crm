<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportPaymentAccounts extends Command
{
    protected $signature = 'import:payment-accounts {--start-id=0 : Starting ID for import} {--debug : Enable debug mode}';
    protected $description = 'Import acm_payment_accounts from old_crm_db into laravelcrm.payment_accounts starting from a specific ID';

    public function handle()
    {
        $startTime = microtime(true);
        $startId = (int) $this->option('start-id');
        $debugMode = $this->option('debug');
        $this->info("Starting import of acm_payment_accounts from ID > {$startId}... Debug mode: " . ($debugMode ? 'ON' : 'OFF'));
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
        $requiredTables = ['users', 'payment_accounts'];
        foreach ($requiredTables as $table) {
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable($table)) {
                $this->error("$table table does not exist in laravelcrm.");
                Log::error("$table table not found");
                return 1;
            }
        }

        // Create skipped_payment_accounts table if it doesn't exist
        if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('skipped_payment_accounts')) {
            DB::connection('mysql')->getSchemaBuilder()->create('skipped_payment_accounts', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('payment_account_id')->nullable();
                $table->string('error_message', 255);
                $table->timestamp('created_at')->useCurrent();
            });
            $this->info('Created skipped_payment_accounts table.');
        }

        // Cache valid user IDs
        $this->info('Caching valid user IDs...');
        $validUserIds = DB::table('users')->pluck('id')->toArray();
        $this->info('Found ' . count($validUserIds) . ' valid users.');

        // Cache user mappings
        $this->info('Building user ID mappings...');
        $userMappingByEmail = DB::connection('old_crm_db')
            ->table('acm_users')
            ->select('id', 'email')
            ->whereIn('id', DB::connection('old_crm_db')->table('acm_payment_accounts')->selectRaw('DISTINCT created_by_user_id'))
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
            ->whereIn('id', DB::connection('old_crm_db')->table('acm_payment_accounts')->selectRaw('DISTINCT created_by_user_id'))
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
            ->table('acm_payment_accounts')
            ->where('id', '>', $startId)
            ->where('isDeleted', 'no')
            ->orderBy('id')
            ->chunk(5000, function ($records) use (&$inserted, &$failed, &$skipped, &$processed, $validUserIds, $userMappingByEmail, $userMappingByName, $startTime, $debugMode) {
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
                        $this->info("Processing Record ID {$record->id}: created_by_user_id={$record->created_by_user_id}");
                    }

                    // Map created_by_user_id
                    $newUserId = $userMappingByEmail[$record->created_by_user_id] ?? $userMappingByName[$record->created_by_user_id] ?? null;

                    // Validate foreign key
                    if (!$newUserId || !in_array($newUserId, $validUserIds)) {
                        $reason = "Invalid created_by_user_id ({$record->created_by_user_id}) - no matching email or name in laravelcrm.users";
                        $skipped[] = "Record ID {$record->id}: $reason";
                        DB::table('skipped_payment_accounts')->insert(['payment_account_id' => $record->id, 'error_message' => $reason, 'created_at' => now()]);
                        Log::warning("Skipped Record ID {$record->id}: $reason");
                        $failed++;
                        continue;
                    }

                    $batch[] = [
                        'id' => $record->id,
                        'account_name' => $record->payment_accounts_name ?? 'Unknown',
                        'account_number' => 'Unknown', // No source field, set default
                        'bank_name' => null,
                        'ifsc_code' => null,
                        'description' => $record->payment_accounts_desc,
                        'status' => 'Active', // Based on isDeleted = 'no'
                        'created_by' => $newUserId,
                        'created_at' => $record->created_at ?: now(),
                        'updated_at' => $record->updated_at ?: now(),
                    ];
                }

                if (!empty($batch)) {
                    try {
                        DB::table('payment_accounts')->upsert(
                            $batch,
                            ['id'],
                            ['account_name', 'account_number', 'bank_name', 'ifsc_code', 'description', 'status', 'created_by', 'updated_at']
                        );
                        $inserted += count($batch);
                        if ($debugMode) {
                            $this->info("Inserted/Updated " . count($batch) . " records");
                        }
                    } catch (\Exception $e) {
                        $reason = "Batch upsert failed: {$e->getMessage()}";
                        $skipped[] = "Batch starting at ID {$batch[0]['id']}: $reason";
                        DB::table('skipped_payment_accounts')->insert(['payment_account_id' => $batch[0]['id'], 'error_message' => $reason, 'created_at' => now()]);
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
            Log::warning('Skipped records during payment accounts import:', array_slice($skipped, 0, 100));
            $this->warn("Skipped records:\n" . implode("\n", array_slice($skipped, 0, 100)) . (count($skipped) > 100 ? "\n...and " . (count($skipped) - 100) . " more" : ""));
        }

        $elapsed = microtime(true) - $startTime;
        $this->info("Import completed. Inserted: $inserted, Failed: $failed, Total Time: " . round($elapsed / 60, 2) . " minutes");
        return 0;
    }
}