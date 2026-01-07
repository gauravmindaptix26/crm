<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportPortfolios extends Command
{
    protected $signature = 'app:import-portfolios';
    protected $description = 'Import portfolios from old CRM database to the new CRM database';

    public function handle()
    {
        $this->info('🔁 Starting portfolios import at ' . Carbon::now()->format('Y-m-d H:i:s') . ' IST...');

        // Step 1: Validate database connections
        try {
            DB::connection('old_crm_db')->getPdo();
            $this->info('✅ Old CRM database connection successful.');
        } catch (\Exception $e) {
            $this->error('❌ Failed to connect to old_crm_db: ' . $e->getMessage());
            Log::error('Failed to connect to old_crm_db: ' . $e->getMessage());
            return 1;
        }
        try {
            DB::connection()->getPdo();
            $this->info('✅ New CRM database connection successful.');
        } catch (\Exception $e) {
            $this->error('❌ Failed to connect to new database: ' . $e->getMessage());
            Log::error('Failed to connect to new database: ' . $e->getMessage());
            return 1;
        }

        // Step 2: Check schema of all_portfolios table
        $this->info('🔍 Checking all_portfolios table schema...');
        try {
            $columns = DB::select('DESCRIBE all_portfolios');
            Log::info('All portfolios table schema: ' . json_encode($columns));
            $this->info('✅ All portfolios table schema: ' . json_encode(array_column($columns, 'Field')));
        } catch (\Exception $e) {
            $this->error('❌ Failed to fetch all_portfolios table schema: ' . $e->getMessage());
            Log::error('Failed to fetch all_portfolios table schema: ' . $e->getMessage());
            return 1;
        }

        // Step 3: Get mapping of old user IDs to new user IDs
        $this->info('🔍 Fetching user ID mappings...');
        $oldToNewUserIdMap = $this->getUserIdMapping();
        if (empty($oldToNewUserIdMap)) {
            $this->warn('⚠️ No user ID mappings found. Continuing with default user ID.');
            Log::warning('No user ID mappings found. Using default user ID.');
        } else {
            $this->info('✅ Found ' . count($oldToNewUserIdMap) . ' user ID mappings.');
            Log::info('User ID mappings: ' . json_encode($oldToNewUserIdMap));
        }

        // Get a default user ID
        $defaultUserId = null;
        try {
            $firstUser = DB::table('users')->select('id')->first();
            if ($firstUser !== null) {
                $defaultUserId = $firstUser->id;
            }
            if ($defaultUserId === null) {
                $this->error('⚠️ No users found in the users table. Please run app:import-users or create a default user.');
                Log::error('No users found in the users table.');
                return 1;
            }
            Log::info('Default user ID: ' . $defaultUserId);
        } catch (\Exception $e) {
            $this->error('❌ Failed to fetch default user ID: ' . $e->getMessage());
            Log::error('Failed to fetch default user ID: ' . $e->getMessage());
            return 1;
        }

        // Step 4: Get default country and department IDs
        $defaultCountryId = 1;
        try {
            $firstCountry = DB::table('countries')->select('id')->first();
            if ($firstCountry !== null) {
                $defaultCountryId = $firstCountry->id;
            }
            Log::info('Default country ID: ' . $defaultCountryId);
        } catch (\Exception $e) {
            $this->warn('⚠️ Failed to fetch default country ID: ' . $e->getMessage() . '. Using default country ID: ' . $defaultCountryId);
            Log::warning('Failed to fetch default country ID: ' . $e->getMessage());
        }

        $defaultDepartmentId = 1;
        try {
            $firstDepartment = DB::table('departments')->select('id')->first();
            if ($firstDepartment !== null) {
                $defaultDepartmentId = $firstDepartment->id;
            }
            Log::info('Default department ID: ' . $defaultDepartmentId);
        } catch (\Exception $e) {
            $this->warn('⚠️ Failed to fetch default department ID: ' . $e->getMessage() . '. Using default department ID: ' . $defaultDepartmentId);
            Log::warning('Failed to fetch default department ID: ' . $e->getMessage());
        }

        // Step 5: Import portfolios from acm_portfolios
        $this->info('📋 Importing portfolios from acm_portfolios...');
        try {
            $oldPortfoliosQuery = DB::connection('old_crm_db')
                ->table('acm_portfolios')
                ->where('isDeleted', 'no')
                ->orderBy('id');

            // Log all portfolios and relevant values
            $allPortfoliosCount = DB::connection('old_crm_db')->table('acm_portfolios')->count();
            $isDeletedValues = DB::connection('old_crm_db')
                ->table('acm_portfolios')
                ->select('isDeleted')
                ->distinct()
                ->pluck('isDeleted')
                ->toArray();
            $createdByUserIds = DB::connection('old_crm_db')
                ->table('acm_portfolios')
                ->select('created_by_user_id')
                ->distinct()
                ->pluck('created_by_user_id')
                ->toArray();
            $countryIds = DB::connection('old_crm_db')
                ->table('acm_portfolios')
                ->select('country_id')
                ->distinct()
                ->pluck('country_id')
                ->toArray();
            $departmentIds = DB::connection('old_crm_db')
                ->table('acm_portfolios')
                ->select('department_id')
                ->distinct()
                ->pluck('department_id')
                ->toArray();
            Log::info('Total portfolios in acm_portfolios (all): ' . $allPortfoliosCount);
            Log::info('Distinct isDeleted values in acm_portfolios: ' . json_encode($isDeletedValues));
            Log::info('Distinct created_by_user_id values in acm_portfolios: ' . json_encode($createdByUserIds));
            Log::info('Distinct country_id values in acm_portfolios: ' . json_encode($countryIds));
            Log::info('Distinct department_id values in acm_portfolios: ' . json_encode($departmentIds));

            $oldPortfolios = $oldPortfoliosQuery->get();
            $totalPortfolios = $oldPortfoliosQuery->count();
            if ($oldPortfolios->isEmpty()) {
                $this->warn('⚠️ No portfolios found in acm_portfolios table.');
                Log::warning('No portfolios found in acm_portfolios table.');
            } else {
                $this->info('🔍 Found ' . $totalPortfolios . ' portfolios to process.');
                Log::info('Portfolios found in acm_portfolios: ' . $totalPortfolios);
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to fetch portfolios from acm_portfolios: ' . $e->getMessage());
            Log::error('Failed to fetch portfolios from acm_portfolios: ' . $e->getMessage());
            return 1;
        }

        $importedCount = 0;
        foreach ($oldPortfolios as $oldPortfolio) {
            // Use default user ID if created_by_user_id is invalid
            $newCreatedById = isset($oldToNewUserIdMap[$oldPortfolio->created_by_user_id]) ? $oldToNewUserIdMap[$oldPortfolio->created_by_user_id] : $defaultUserId;
            if (!isset($oldToNewUserIdMap[$oldPortfolio->created_by_user_id])) {
                $this->warn("⚠️ Portfolio ID {$oldPortfolio->id}: Invalid created_by_user_id {$oldPortfolio->created_by_user_id}. Using default user ID {$defaultUserId}.");
                Log::warning("Portfolio ID {$oldPortfolio->id}: Invalid created_by_user_id {$oldPortfolio->created_by_user_id}. Using default user ID {$defaultUserId}.");
            }

            // Use default country and department IDs if invalid
            $newCountryId = $oldPortfolio->country_id > 0 && DB::table('countries')->where('id', $oldPortfolio->country_id)->exists() ? $oldPortfolio->country_id : $defaultCountryId;
            if ($oldPortfolio->country_id <= 0 || !DB::table('countries')->where('id', $oldPortfolio->country_id)->exists()) {
                $this->warn("⚠️ Portfolio ID {$oldPortfolio->id}: Invalid country_id {$oldPortfolio->country_id}. Using default country ID {$defaultCountryId}.");
                Log::warning("Portfolio ID {$oldPortfolio->id}: Invalid country_id {$oldPortfolio->country_id}. Using default country ID {$defaultCountryId}.");
            }

            $newDepartmentId = $oldPortfolio->department_id > 0 && DB::table('departments')->where('id', $oldPortfolio->department_id)->exists() ? $oldPortfolio->department_id : $defaultDepartmentId;
            if ($oldPortfolio->department_id <= 0 || !DB::table('departments')->where('id', $oldPortfolio->department_id)->exists()) {
                $this->warn("⚠️ Portfolio ID {$oldPortfolio->id}: Invalid department_id {$oldPortfolio->department_id}. Using default department ID {$defaultDepartmentId}.");
                Log::warning("Portfolio ID {$oldPortfolio->id}: Invalid department_id {$oldPortfolio->department_id}. Using default department ID {$defaultDepartmentId}.");
            }

            // Format dates
            $createdAt = $this->formatDate($oldPortfolio->created_at, 'all_portfolios', $oldPortfolio->id, false);
            $updatedAt = $this->formatDate($oldPortfolio->updated_at, 'all_portfolios', $oldPortfolio->id, true);

            // Log date values
            Log::info("Portfolio ID {$oldPortfolio->id}: Old created_at={$oldPortfolio->created_at}, Formatted={$createdAt}, Old updated_at={$oldPortfolio->updated_at}, Formatted={$updatedAt}");

            // Handle attachment_name
            $attachment = $oldPortfolio->attachment_name ? substr($oldPortfolio->attachment_name, 0, 255) : null;

            // Prepare portfolio data
            $portfolioData = [
                'id' => $oldPortfolio->id,
                'title' => $oldPortfolio->name ? substr($oldPortfolio->name, 0, 255) : 'Untitled Portfolio',
                'description' => $oldPortfolio->description,
                'country_id' => $newCountryId,
                'department_id' => $newDepartmentId,
                'attachment' => $attachment,
                'created_by' => $newCreatedById,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            // Insert or update portfolio using raw SQL
            try {
                DB::transaction(function () use ($portfolioData) {
                    Log::info("Inserting portfolio ID {$portfolioData['id']}: " . json_encode($portfolioData));
                    DB::statement(
                        'INSERT INTO all_portfolios (id, title, country_id, department_id, description, attachment, created_by, created_at, updated_at) ' .
                        'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ' .
                        'ON DUPLICATE KEY UPDATE ' .
                        'title = VALUES(title), country_id = VALUES(country_id), department_id = VALUES(department_id), ' .
                        'description = VALUES(description), attachment = VALUES(attachment), ' .
                        'created_by = VALUES(created_by), created_at = VALUES(created_at), updated_at = VALUES(updated_at)',
                        [
                            $portfolioData['id'],
                            $portfolioData['title'],
                            $portfolioData['country_id'],
                            $portfolioData['department_id'],
                            $portfolioData['description'],
                            $portfolioData['attachment'],
                            $portfolioData['created_by'],
                            $portfolioData['created_at'],
                            $portfolioData['updated_at'],
                        ]
                    );
                });

                $importedCount++;
                $this->line("✅ Imported portfolio: " . ($oldPortfolio->name ? $oldPortfolio->name : 'Untitled Portfolio') . " (ID: {$oldPortfolio->id}, Created: {$createdAt}, Attachment: " . ($attachment ?? 'None') . ")");
            } catch (\Exception $e) {
                $this->error("❌ Failed to import portfolio ID {$oldPortfolio->id}: {$e->getMessage()}");
                Log::error("Failed to import portfolio ID {$oldPortfolio->id}: {$e->getMessage()}");
            }
        }

        $this->info('✅ Imported ' . $importedCount . ' portfolios.');
        Log::info('Imported portfolios count: ' . $importedCount);

        // Verify total records
        $portfolioCount = DB::table('all_portfolios')->count();
        $this->info("📊 Total records in all_portfolios: {$portfolioCount}");

        // Verify no null created_at values
        $nullCreatedAtCount = DB::table('all_portfolios')->whereNull('created_at')->count();
        if ($nullCreatedAtCount > 0) {
            $this->error("❌ Found {$nullCreatedAtCount} portfolios with null created_at. Fixing now...");
            Log::error("Found {$nullCreatedAtCount} portfolios with null created_at.");
            $nullPortfolioIds = DB::table('all_portfolios')->whereNull('created_at')->pluck('id')->toArray();
            Log::error("Portfolios with null created_at: " . json_encode($nullPortfolioIds));
            DB::table('all_portfolios')
                ->whereNull('created_at')
                ->update(['created_at' => Carbon::now()->format('Y-m-d H:i:s')]);
            $this->info("✅ Fixed {$nullCreatedAtCount} portfolios by setting created_at to current timestamp.");
        } else {
            $this->info("✅ No portfolios with null created_at found.");
        }

        return 0;
    }

    /**
     * Get mapping of old user IDs to new user IDs from the users table.
     *
     * @return array
     */
    protected function getUserIdMapping()
    {
        $mapping = [];
        try {
            $users = DB::table('users')->select('id', 'email')->get();
            foreach ($users as $user) {
                $oldUserId = DB::connection('old_crm_db')
                    ->table('acm_users')
                    ->where('email', $user->email)
                    ->value('id');

                if ($oldUserId !== null) {
                    $mapping[$oldUserId] = $user->id;
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to fetch user mappings: ' . $e->getMessage());
            Log::error('Failed to fetch user mappings: ' . $e->getMessage());
        }
        return $mapping;
    }

    /**
     * Format date to preserve original timestamp with current date as fallback.
     *
     * @param mixed $value
     * @param string $table
     * @param int $recordId
     * @param bool $isUpdatedAt
     * @return string|null
     */
    protected function formatDate($value, $table, $recordId, $isUpdatedAt = false)
    {
        $currentDate = Carbon::now()->format('Y-m-d H:i:s');
        if ($isUpdatedAt) {
            if ($value === null || trim((string)$value) === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00' || strtoupper((string)$value) === 'NULL') {
                Log::warning("Invalid updated_at value in {$table} ID {$recordId}: {$value}. Using null for updated_at.");
                return null;
            }
            try {
                $date = new \DateTime($value);
                return $date->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                Log::warning("Failed to parse updated_at in {$table} ID {$recordId}: {$value}, Error: {$e->getMessage()}. Using null for updated_at.");
                return null;
            }
        } else {
            if ($value === null || trim((string)$value) === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00' || strtoupper((string)$value) === 'NULL') {
                Log::warning("Invalid created_at value in {$table} ID {$recordId}: {$value}. Using current date {$currentDate} for created_at.");
                return $currentDate;
            }
            try {
                $date = new \DateTime($value);
                return $date->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                Log::warning("Failed to parse created_at in {$table} ID {$recordId}: {$value}, Error: {$e->getMessage()}. Using current date {$currentDate} for created_at.");
                return $currentDate;
            }
        }
    }
}