<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportRndDetails extends Command
{
    protected $signature = 'app:import-rnd-details';
    protected $description = 'Import R&D details from old CRM database to the new CRM database';

    public function handle()
    {
        $this->info('🔁 Starting R&D details import at ' . Carbon::now()->format('Y-m-d H:i:s') . ' IST...');

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

        // Step 2: Check schema of all_rnds table
        $this->info('🔍 Checking all_rnds table schema...');
        try {
            $columns = DB::select('DESCRIBE all_rnds');
            Log::info('All RNDs table schema: ' . json_encode($columns));
            $this->info('✅ All RNDs table schema: ' . json_encode(array_column($columns, 'Field')));
        } catch (\Exception $e) {
            $this->error('❌ Failed to fetch all_rnds table schema: ' . $e->getMessage());
            Log::error('Failed to fetch all_rnds table schema: ' . $e->getMessage());
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

        // Step 4: Get a default department ID
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

        // Step 5: Import R&D details from acm_rnd_details
        $this->info('📋 Importing R&D details from acm_rnd_details...');
        try {
            $oldRndDetailsQuery = DB::connection('old_crm_db')
                ->table('acm_rnd_details')
                ->where('isDeleted', 'no')
                ->orderBy('id');

            // Log all R&D details and relevant values
            $allRndDetailsCount = DB::connection('old_crm_db')->table('acm_rnd_details')->count();
            $isDeletedValues = DB::connection('old_crm_db')
                ->table('acm_rnd_details')
                ->select('isDeleted')
                ->distinct()
                ->pluck('isDeleted')
                ->toArray();
            $createdByUserIds = DB::connection('old_crm_db')
                ->table('acm_rnd_details')
                ->select('created_by_user_id')
                ->distinct()
                ->pluck('created_by_user_id')
                ->toArray();
            $departmentIds = DB::connection('old_crm_db')
                ->table('acm_rnd_details')
                ->select('department_id')
                ->distinct()
                ->pluck('department_id')
                ->toArray();
            Log::info('Total R&D details in acm_rnd_details (all): ' . $allRndDetailsCount);
            Log::info('Distinct isDeleted values in acm_rnd_details: ' . json_encode($isDeletedValues));
            Log::info('Distinct created_by_user_id values in acm_rnd_details: ' . json_encode($createdByUserIds));
            Log::info('Distinct department_id values in acm_rnd_details: ' . json_encode($departmentIds));

            $oldRndDetails = $oldRndDetailsQuery->get();
            $totalRndDetails = $oldRndDetailsQuery->count();
            if ($oldRndDetails->isEmpty()) {
                $this->warn('⚠️ No R&D details found in acm_rnd_details table.');
                Log::warning('No R&D details found in acm_rnd_details table.');
            } else {
                $this->info('🔍 Found ' . $totalRndDetails . ' R&D details to process.');
                Log::info('R&D details found in acm_rnd_details: ' . $totalRndDetails);
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to fetch R&D details from acm_rnd_details: ' . $e->getMessage());
            Log::error('Failed to fetch R&D details from acm_rnd_details: ' . $e->getMessage());
            return 1;
        }

        $importedCount = 0;
        foreach ($oldRndDetails as $oldRnd) {
            // Use default user ID if created_by_user_id is invalid
            $newCreatedById = isset($oldToNewUserIdMap[$oldRnd->created_by_user_id]) ? $oldToNewUserIdMap[$oldRnd->created_by_user_id] : $defaultUserId;
            if (!isset($oldToNewUserIdMap[$oldRnd->created_by_user_id])) {
                $this->warn("⚠️ R&D ID {$oldRnd->id}: Invalid created_by_user_id {$oldRnd->created_by_user_id}. Using default user ID {$defaultUserId}.");
                Log::warning("R&D ID {$oldRnd->id}: Invalid created_by_user_id {$oldRnd->created_by_user_id}. Using default user ID {$defaultUserId}.");
            }

            // Use default department ID if department_id is 0 or invalid
            $newDepartmentId = ($oldRnd->department_id > 0 && DB::table('departments')->where('id', $oldRnd->department_id)->exists()) ? $oldRnd->department_id : $defaultDepartmentId;
            if ($oldRnd->department_id <= 0 || !DB::table('departments')->where('id', $oldRnd->department_id)->exists()) {
                $this->warn("⚠️ R&D ID {$oldRnd->id}: Invalid department_id {$oldRnd->department_id}. Using default department ID {$defaultDepartmentId}.");
                Log::warning("R&D ID {$oldRnd->id}: Invalid department_id {$oldRnd->department_id}. Using default department ID {$defaultDepartmentId}.");
            }

            // Format dates
            $createdAt = $this->formatDate($oldRnd->created_at, 'all_rnds', $oldRnd->id, false);
            $updatedAt = $this->formatDate($oldRnd->updated_at, 'all_rnds', $oldRnd->id, true);

            // Log date values
            Log::info("R&D ID {$oldRnd->id}: Old created_at={$oldRnd->created_at}, Formatted={$createdAt}, Old updated_at={$oldRnd->updated_at}, Formatted={$updatedAt}");

            // Prepare R&D data
            $rndData = [
                'id' => $oldRnd->id,
                'title' => $oldRnd->rnd_title ? $oldRnd->rnd_title : 'Untitled R&D',
                'description' => $oldRnd->rnd_description ?: null,
                'urls' => $oldRnd->rnd_urls,
                'department_id' => $newDepartmentId,
                'attachment' => null, // No equivalent in source
                'created_by' => $newCreatedById,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            // Insert or update R&D details using raw SQL
            try {
                DB::transaction(function () use ($rndData) {
                    // Log R&D data before insert
                    Log::info("Inserting R&D ID {$rndData['id']}: " . json_encode($rndData));
                    DB::statement(
                        'INSERT INTO all_rnds (id, title, description, urls, department_id, attachment, created_by, created_at, updated_at) ' .
                        'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ' .
                        'ON DUPLICATE KEY UPDATE ' .
                        'title = VALUES(title), description = VALUES(description), urls = VALUES(urls), ' .
                        'department_id = VALUES(department_id), attachment = VALUES(attachment), ' .
                        'created_by = VALUES(created_by), created_at = VALUES(created_at), updated_at = VALUES(updated_at)',
                        [
                            $rndData['id'],
                            $rndData['title'],
                            $rndData['description'],
                            $rndData['urls'],
                            $rndData['department_id'],
                            $rndData['attachment'],
                            $rndData['created_by'],
                            $rndData['created_at'],
                            $rndData['updated_at'],
                        ]
                    );
                });

                $importedCount++;
                $this->line("✅ Imported R&D: " . ($oldRnd->rnd_title ? $oldRnd->rnd_title : 'Untitled R&D') . " (ID: {$oldRnd->id}, Created: {$createdAt}, Updated: {$updatedAt})");
            } catch (\Exception $e) {
                $this->error("❌ Failed to import R&D ID {$oldRnd->id}: {$e->getMessage()}");
                Log::error("Failed to import R&D ID {$oldRnd->id}: {$e->getMessage()}");
            }
        }

        $this->info('✅ Imported ' . $importedCount . ' R&D details.');
        Log::info('Imported R&D details count: ' . $importedCount);

        // Verify total records
        $rndCount = DB::table('all_rnds')->count();
        $this->info("📊 Total records in all_rnds: {$rndCount}");

        // Verify no null created_at values
        $nullCreatedAtCount = DB::table('all_rnds')->whereNull('created_at')->count();
        if ($nullCreatedAtCount > 0) {
            $this->error("❌ Found {$nullCreatedAtCount} R&D records with null created_at. Fixing now...");
            Log::error("Found {$nullCreatedAtCount} R&D records with null created_at.");
            $nullRndIds = DB::table('all_rnds')->whereNull('created_at')->pluck('id')->toArray();
            Log::error("R&D records with null created_at: " . json_encode($nullRndIds));
            DB::table('all_rnds')
                ->whereNull('created_at')
                ->update(['created_at' => Carbon::now()->format('Y-m-d H:i:s')]);
            $this->info("✅ Fixed {$nullCreatedAtCount} R&D records by setting created_at to current timestamp.");
        } else {
            $this->info("✅ No R&D records with null created_at found.");
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
            // For updated_at, allow null if the value is invalid
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
            // For created_at, always return a valid timestamp
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