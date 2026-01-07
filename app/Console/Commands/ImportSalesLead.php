<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ImportSalesLead extends Command
{
    protected $signature = 'import:sales-leads {--reset-hired-froms : Clear laravelcrm.hired_froms before import}';
    protected $description = 'Import sales leads from old_crm_db.acm_sales_lead to laravelcrm.sales_leads with proper hired_from mapping and nullable lead_from_id';

    public function handle()
    {
        $this->info('🔁 Starting sales leads import...');

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

        // Check required tables
        $requiredTables = ['sales_leads', 'users', 'countries', 'departments', 'hired_froms'];
        foreach ($requiredTables as $table) {
            if (!Schema::connection('mysql')->hasTable($table)) {
                $this->error("$table table does not exist in laravelcrm.");
                Log::error("$table table not found");
                return 1;
            }
        }
        if (!DB::connection('old_crm_db')->getSchemaBuilder()->hasTable('acm_sales_lead')) {
            $this->error("acm_sales_lead table does not exist in old_crm_db.");
            Log::error("acm_sales_lead table not found");
            return 1;
        }

        // Create skipped_sales_leads table if it doesn't exist
        if (!Schema::connection('mysql')->hasTable('skipped_sales_leads')) {
            $this->info('Creating skipped_sales_leads table...');
            Schema::connection('mysql')->create('skipped_sales_leads', function ($table) {
                $table->bigIncrements('id');
                $table->bigInteger('lead_id')->unsigned()->nullable();
                $table->string('client_name', 255)->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
            $this->info('skipped_sales_leads table created.');
        }

        // Cache valid IDs
        $validUserIds = DB::connection('mysql')->table('users')->pluck('id')->toArray();
        $validCountryIds = DB::connection('mysql')->table('countries')->pluck('id')->toArray();
        $validDepartmentIds = DB::connection('mysql')->table('departments')->pluck('id')->toArray();
        $validLeadFromIds = DB::connection('mysql')->table('hired_froms')->pluck('id')->toArray();

        // Default IDs for non-nullable fields
        $defaultUserId = 22369; // Mandeep Singh
        $defaultCountryId = !empty($validCountryIds) ? min($validCountryIds) : 3;
        $defaultDepartmentId = !empty($validDepartmentIds) ? min($validDepartmentIds) : 4;

        // Validate defaults
        if (!in_array($defaultUserId, $validUserIds)) {
            $this->error("Default user ID $defaultUserId not found in laravelcrm.users.");
            Log::error("Default user ID $defaultUserId not found");
            return 1;
        }
        if (!$defaultCountryId) {
            $this->error("No valid country_id found in laravelcrm.countries.");
            Log::error("No valid country_id found");
            return 1;
        }
        if (!$defaultDepartmentId) {
            $this->error("No valid department_id found in laravelcrm.departments.");
            Log::error("No valid department_id found");
            return 1;
        }

        // Map old user IDs to new user IDs based on email or name
        $userIdMap = [];
        $oldUsers = DB::connection('old_crm_db')->table('acm_users')->select('id', 'name', 'email')->get();
        $newUsers = DB::connection('mysql')->table('users')->select('id', 'name', 'email')->get();
        foreach ($oldUsers as $oldUser) {
            foreach ($newUsers as $newUser) {
                if ($oldUser->email === $newUser->email || $oldUser->name === $newUser->name) {
                    $userIdMap[$oldUser->id] = $newUser->id;
                    break;
                }
            }
        }
        $unmatchedUsers = array_diff(array_keys($userIdMap), array_keys($oldUsers->pluck('id')->toArray()));
        $this->info("Mapped " . count($userIdMap) . " users. Unmatched users: " . count($unmatchedUsers));

        // Optionally reset hired_froms table
        if ($this->option('reset-hired-froms')) {
            $this->info('Clearing laravelcrm.hired_froms table...');
            DB::connection('mysql')->statement('TRUNCATE TABLE laravelcrm.hired_froms');
            $this->info('laravelcrm.hired_froms table cleared.');
            $validLeadFromIds = [];
        }

        // Import acm_hiredfrom into hired_froms
        $this->info('Importing hired_from records from old_crm_db.acm_hiredfrom to laravelcrm.hired_froms...');
        try {
            DB::connection('mysql')->statement("
                INSERT IGNORE INTO laravelcrm.hired_froms (id, name, description, created_at, updated_at)
                SELECT id, name, `desc`,
                    COALESCE(NULLIF(created_at, '0000-00-00 00:00:00'), NOW()) AS created_at,
                    COALESCE(NULLIF(updated_at, '0000-00-00 00:00:00'), NOW()) AS updated_at
                FROM old_crm_db.acm_hiredfrom
                WHERE isDeleted = 'no'
                AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ");
            $newHiredFroms = DB::connection('mysql')->table('hired_froms')->where('created_at', '>=', now()->subMinutes(5))->count();
            $this->info("Imported $newHiredFroms new hired_froms with proper names.");
        } catch (\Exception $e) {
            $this->warn("Failed to import hired_froms: {$e->getMessage()}");
            Log::error("Failed to import hired_froms: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            $invalidHiredFroms = DB::connection('old_crm_db')
                ->table('acm_hiredfrom')
                ->where('isDeleted', 'no')
                ->where(function ($query) {
                    $query->where('created_at', '0000-00-00 00:00:00')
                          ->orWhere('updated_at', '0000-00-00 00:00:00')
                          ->orWhere('deleted_at', '0000-00-00 00:00:00');
                })
                ->select('id', 'name', 'created_at', 'updated_at', 'deleted_at')
                ->get();
            Log::warning("Found invalid DATETIME values in acm_hiredfrom", ['records' => $invalidHiredFroms]);
        }

        // Update validLeadFromIds after import
        $validLeadFromIds = DB::connection('mysql')->table('hired_froms')->pluck('id')->toArray();

        // Import missing departments
        $this->info('Importing missing department IDs into laravelcrm.departments...');
        try {
            DB::connection('mysql')->statement("
                INSERT IGNORE INTO laravelcrm.departments (id, name, created_at, updated_at)
                SELECT DISTINCT project_department, CONCAT('Department ', project_department), NOW(), NOW()
                FROM old_crm_db.acm_sales_lead
                WHERE project_department IS NOT NULL 
                AND project_department NOT IN (SELECT id FROM laravelcrm.departments)
            ");
            $newDepartments = DB::connection('mysql')->table('departments')->where('created_at', '>=', now()->subMinutes(5))->count();
            $this->info("Imported $newDepartments new departments.");
        } catch (\Exception $e) {
            $this->warn("Failed to import departments: {$e->getMessage()}");
            Log::error("Failed to import departments: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
        }

        // Fetch leads
        $oldLeads = DB::connection('old_crm_db')
            ->table('acm_sales_lead')
            ->where('isDeleted', 'no')
            ->where(function ($query) {
                $query->whereNull('deleted_at')
                      ->orWhere('deleted_at', '0000-00-00 00:00:00');
            })
            ->get();

        $total = $oldLeads->count();
        $this->info("Found {$total} sales leads to import.");

        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        $batch = [];
        $batchSize = 100;
        $invalidStatuses = [];
        $invalidLeadFroms = [];
        $invalidUsers = [];

        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($oldLeads as $index => $oldLead) {
            try {
                // Skip if lead already exists
                if (DB::connection('mysql')->table('sales_leads')->where('id', $oldLead->id)->exists()) {
                    $this->info("Lead ID {$oldLead->id} already exists, skipping.");
                    Log::info("Lead ID {$oldLead->id} skipped due to existing ID");
                    $skippedCount++;
                    continue;
                }

                // Skip if required fields are empty or invalid
                if (empty($oldLead->client_name) || empty($oldLead->job_title) || empty($oldLead->client_email)) {
                    $this->warn("Lead ID {$oldLead->id} has empty required fields (client_name, job_title, or client_email), skipping.");
                    Log::warning("Lead ID {$oldLead->id} has empty required fields", [
                        'client_name' => $oldLead->client_name,
                        'job_title' => $oldLead->job_title,
                        'client_email' => $oldLead->client_email,
                    ]);
                    DB::connection('mysql')->table('skipped_sales_leads')->insert([
                        'lead_id' => $oldLead->id,
                        'client_name' => substr(trim($oldLead->client_name ?? ''), 0, 255),
                        'error_message' => 'Missing required fields: client_name, job_title, or client_email',
                        'created_at' => now(),
                    ]);
                    $skippedCount++;
                    continue;
                }

                // Map sales_person_id
                $salesPersonId = isset($userIdMap[$oldLead->project_sale_person])
                    ? $userIdMap[$oldLead->project_sale_person]
                    : $defaultUserId;
                if (!in_array($salesPersonId, $validUserIds)) {
                    $this->warn("Invalid sales_person_id {$oldLead->project_sale_person} for lead ID {$oldLead->id}, using default ID {$defaultUserId}");
                    Log::warning("Invalid sales_person_id {$oldLead->project_sale_person} for lead ID {$oldLead->id}", [
                        'old_sale_person' => $oldLead->project_sale_person,
                        'default_id' => $defaultUserId,
                    ]);
                    $invalidUsers[$oldLead->id] = $oldLead->project_sale_person;
                    $salesPersonId = $defaultUserId;
                }

                // Map client_type
                $validClientTypes = ['Reseller', 'Premium', 'General'];
                $clientType = match (strtolower($oldLead->client_type ?? '')) {
                    'reseller' => 'Reseller',
                    'premium' => 'Premium',
                    default => 'General',
                };
                if (!in_array(strtolower($oldLead->client_type), array_map('strtolower', $validClientTypes)) && !empty($oldLead->client_type)) {
                    $this->warn("Invalid client_type '{$oldLead->client_type}' for lead ID {$oldLead->id}, using 'General'");
                    Log::warning("Invalid client_type '{$oldLead->client_type}' for lead ID {$oldLead->id}, using 'General'");
                }

                // Map status
                $validStatuses = ['Bid', 'Progress', 'Hired'];
                $status = in_array(strtolower($oldLead->project_status), array_map('strtolower', $validStatuses))
                    ? ucfirst(strtolower($oldLead->project_status))
                    : null;
                if (!$status && !empty($oldLead->project_status)) {
                    $this->warn("Invalid project_status '{$oldLead->project_status}' for lead ID {$oldLead->id}, setting to NULL");
                    Log::warning("Invalid project_status '{$oldLead->project_status}' for lead ID {$oldLead->id}, setting to NULL");
                    $invalidStatuses[$oldLead->id] = $oldLead->project_status;
                }

                // Map country_id
                $countryId = in_array($oldLead->country_id, $validCountryIds)
                    ? $oldLead->country_id
                    : $defaultCountryId;
                if ($oldLead->country_id && $countryId === $defaultCountryId) {
                    $this->warn("Invalid country_id {$oldLead->country_id} for lead ID {$oldLead->id}, using default ID {$defaultCountryId}");
                    Log::warning("Invalid country_id {$oldLead->country_id} for lead ID {$oldLead->id}, using default ID {$defaultCountryId}");
                }

                // Map department_id
                $departmentId = in_array($oldLead->project_department, $validDepartmentIds)
                    ? $oldLead->project_department
                    : $defaultDepartmentId;
                if ($oldLead->project_department && $departmentId === $defaultDepartmentId) {
                    $this->warn("Invalid project_department {$oldLead->project_department} for lead ID {$oldLead->id}, using default ID {$defaultDepartmentId}");
                    Log::warning("Invalid project_department {$oldLead->project_department} for lead ID {$oldLead->id}, using default ID {$defaultDepartmentId}");
                }

                // Map lead_from_id (set to NULL if invalid or missing)
                $leadFromId = null;
                if ($oldLead->project_hired_from && in_array($oldLead->project_hired_from, $validLeadFromIds)) {
                    $leadFromId = $oldLead->project_hired_from;
                } else if ($oldLead->project_hired_from) {
                    $this->warn("Invalid project_hired_from {$oldLead->project_hired_from} for lead ID {$oldLead->id}, setting to NULL");
                    Log::warning("Invalid project_hired_from {$oldLead->project_hired_from} for lead ID {$oldLead->id}, setting to NULL");
                    $invalidLeadFroms[$oldLead->id] = $oldLead->project_hired_from;
                }

                // Map status_update_date
                $statusUpdateDate = null;
                if ($oldLead->status_update_date && $oldLead->status_update_date !== '0000-00-00 00:00:00' && $oldLead->status_update_date !== '-0001-11-30') {
                    try {
                        $statusUpdateDate = Carbon::parse($oldLead->status_update_date)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $this->warn("Invalid status_update_date '{$oldLead->status_update_date}' for lead ID {$oldLead->id}, setting to NULL");
                        Log::warning("Invalid status_update_date for lead ID {$oldLead->id}", [
                            'status_update_date' => $oldLead->status_update_date,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Preserve timestamps
                $createdAt = $oldLead->created_at && $oldLead->created_at !== '0000-00-00 00:00:00'
                    ? Carbon::parse($oldLead->created_at)->format('Y-m-d H:i:s')
                    : now();
                $updatedAt = $oldLead->updated_at && $oldLead->updated_at !== '0000-00-00 00:00:00'
                    ? Carbon::parse($oldLead->updated_at)->format('Y-m-d H:i:s')
                    : $createdAt;

                // Handle client_phone (allow NULL in new schema)
                $clientPhone = !empty($oldLead->client_phone) && $oldLead->client_phone !== 'None' ? substr($oldLead->client_phone, 0, 255) : null;

                // Truncate strings to fit schema
                $clientName = substr(trim($oldLead->client_name), 0, 255);
                $clientEmail = substr(trim($oldLead->client_email), 0, 255);
                $jobTitle = substr(trim($oldLead->job_title), 0, 255);
                $jobUrl = !empty($oldLead->job_post_url) ? substr(trim($oldLead->job_post_url), 0, 255) : null;
                $statusReason = !empty($oldLead->reason_description) ? $oldLead->reason_description : null;

                $data = [
                    'id' => $oldLead->id,
                    'client_name' => $clientName,
                    'client_email' => $clientEmail,
                    'client_phone' => $clientPhone,
                    'job_title' => $jobTitle,
                    'description' => $oldLead->description,
                    'job_url' => $jobUrl,
                    'client_type' => $clientType,
                    'lead_from_id' => $leadFromId,
                    'country_id' => $countryId,
                    'department_id' => $departmentId,
                    'sales_person_id' => $salesPersonId,
                    'status' => $status,
                    'status_update_date' => $statusUpdateDate,
                    'status_reason' => $statusReason,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ];

                // Validate required fields
                $requiredFields = ['client_name', 'client_email', 'job_title', 'client_type', 'country_id', 'department_id', 'sales_person_id'];
                $canProceed = true;
                foreach ($requiredFields as $field) {
                    if (is_null($data[$field])) {
                        $this->warn("Skipping lead ID {$oldLead->id}: {$field} is NULL");
                        Log::warning("Skipping lead ID {$oldLead->id}: {$field} is NULL", ['lead' => (array)$oldLead]);
                        DB::connection('mysql')->table('skipped_sales_leads')->insert([
                            'lead_id' => $oldLead->id,
                            'client_name' => $clientName,
                            'error_message' => "Missing required field: $field",
                            'created_at' => now(),
                        ]);
                        $skippedCount++;
                        $canProceed = false;
                        break;
                    }
                }

                if ($canProceed) {
                    $batch[] = $data;
                }

                if (count($batch) >= $batchSize || $index == count($oldLeads) - 1) {
                    try {
                        $values = array_map(function ($item) {
                            return '(' . implode(',', array_map(function ($value) {
                                return is_null($value) ? 'NULL' : DB::connection('mysql')->getPdo()->quote($value);
                            }, $item)) . ')';
                        }, $batch);
                        $columns = implode(',', array_keys($data));
                        $sql = "INSERT IGNORE INTO laravelcrm.sales_leads ($columns) VALUES " . implode(',', $values);
                        $affectedRows = DB::connection('mysql')->statement($sql);
                        $successCount += $affectedRows;
                        $this->info("Inserted batch of $affectedRows leads (batch size: " . count($batch) . ")");
                        if ($affectedRows < count($batch)) {
                            $skippedCount += (count($batch) - $affectedRows);
                            Log::warning("Some leads skipped in batch due to duplicates or constraints", [
                                'batch_size' => count($batch),
                                'inserted' => $affectedRows,
                                'skipped_ids' => array_column($batch, 'id'),
                            ]);
                        }
                    } catch (\Exception $e) {
                        $this->error("Batch insertion failed: {$e->getMessage()}");
                        Log::error("Batch insertion failed: {$e->getMessage()}", [
                            'batch_size' => count($batch),
                            'sql' => $sql,
                            'trace' => $e->getTraceAsString(),
                            'batch_ids' => array_column($batch, 'id'),
                        ]);
                        foreach ($batch as $batchData) {
                            DB::connection('mysql')->table('skipped_sales_leads')->insert([
                                'lead_id' => $batchData['id'],
                                'client_name' => $batchData['client_name'],
                                'error_message' => $e->getMessage(),
                                'created_at' => now(),
                            ]);
                        }
                        $skippedCount += count($batch);
                        $errorCount += count($batch);
                    }
                    $batch = [];
                }
            } catch (\Exception $e) {
                $this->error("Failed to process lead ID {$oldLead->id}: {$e->getMessage()}");
                Log::error("Failed to process lead ID {$oldLead->id}: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString(),
                    'data' => (array)$oldLead,
                ]);
                DB::connection('mysql')->table('skipped_sales_leads')->insert([
                    'lead_id' => $oldLead->id,
                    'client_name' => substr(trim($oldLead->client_name ?? ''), 0, 255),
                    'error_message' => $e->getMessage(),
                    'created_at' => now(),
                ]);
                $skippedCount++;
            }
        }

        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');

        // Validate foreign keys post-import (skip lead_from_id since it can be NULL)
        $this->info('Validating foreign keys...');
        $invalidForeignKeys = DB::connection('mysql')
            ->table('sales_leads')
            ->whereNotIn('sales_person_id', $validUserIds)
            ->orWhereNotIn('country_id', $validCountryIds)
            ->orWhereNotIn('department_id', $validDepartmentIds)
            ->pluck('id')
            ->toArray();
        if (!empty($invalidForeignKeys)) {
            $this->warn("Found " . count($invalidForeignKeys) . " leads with invalid foreign keys.");
            Log::warning("Leads with invalid foreign keys", ['ids' => $invalidForeignKeys]);
            DB::connection('mysql')
                ->table('sales_leads')
                ->whereIn('id', $invalidForeignKeys)
                ->update([
                    'sales_person_id' => $defaultUserId,
                    'country_id' => $defaultCountryId,
                    'department_id' => $defaultDepartmentId,
                    'updated_at' => now(),
                ]);
            $this->info("Updated " . count($invalidForeignKeys) . " leads with default foreign key IDs.");
        }

        // Log summary
        if (!empty($invalidStatuses)) {
            $this->warn("Found " . count($invalidStatuses) . " leads with invalid statuses.");
            Log::warning("Invalid statuses found", ['statuses' => $invalidStatuses]);
        }
        if (!empty($invalidLeadFroms)) {
            $this->warn("Found " . count($invalidLeadFroms) . " leads with invalid project_hired_from values (set to NULL).");
            Log::warning("Invalid project_hired_from values set to NULL", ['lead_froms' => $invalidLeadFroms]);
        }
        if (!empty($invalidUsers)) {
            $this->warn("Found " . count($invalidUsers) . " leads with invalid user IDs.");
            Log::warning("Invalid user IDs found", ['users' => $invalidUsers]);
        }

        $totalRecords = DB::connection('mysql')->table('sales_leads')->count();
        $newRecords = DB::connection('mysql')
            ->table('sales_leads')
            ->where('created_at', '>=', now()->subDays(1))
            ->count();

        $this->info("✅ Import completed. Successfully imported: $successCount, Skipped: $skippedCount, Failed: $errorCount");
        $this->info("Total records in sales_leads: $totalRecords");
        $this->info("New records inserted in this run (last 24 hours): $newRecords");
        $this->info("Unmatched users: " . count($invalidUsers));
        return $errorCount > 0 ? 1 : 0;
    }
}