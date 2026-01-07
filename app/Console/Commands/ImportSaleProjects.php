<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ImportSaleProjects extends Command
{
    protected $signature = 'app:import-sale-projects {--reset-hired-froms : Clear laravelcrm.hired_froms before import}';
    protected $description = 'Import projects from old_crm_db.acm_sale_projects to laravelcrm.sale_team_projects with correct sales_person_id and hired_from mappings';

    public function handle()
    {
        $this->info('🔁 Starting project import...');

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
        $requiredTables = ['sale_team_projects', 'users', 'countries', 'departments', 'hired_froms'];
        foreach ($requiredTables as $table) {
            if (!Schema::connection('mysql')->hasTable($table)) {
                $this->error("$table table does not exist in laravelcrm.");
                Log::error("$table table not found");
                return 1;
            }
        }
        if (!DB::connection('old_crm_db')->getSchemaBuilder()->hasTable('acm_sale_projects')) {
            $this->error("acm_sale_projects table does not exist in old_crm_db.");
            Log::error("acm_sale_projects table not found");
            return 1;
        }

        // Create skipped_sale_projects table if it doesn't exist
        if (!Schema::connection('mysql')->hasTable('skipped_sale_projects')) {
            $this->info('Creating skipped_sale_projects table...');
            Schema::connection('mysql')->create('skipped_sale_projects', function ($table) {
                $table->bigIncrements('id');
                $table->bigInteger('project_id')->unsigned()->nullable();
                $table->string('project_name', 255)->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
            $this->info('skipped_sale_projects table created.');
        }

        // Cache valid IDs
        $validUserIds = DB::connection('mysql')->table('users')->pluck('id')->toArray();
        $validCountryIds = DB::connection('mysql')->table('countries')->pluck('id')->toArray();
        $validDepartmentIds = DB::connection('mysql')->table('departments')->pluck('id')->toArray();
        $validHiredFromIds = DB::connection('mysql')->table('hired_froms')->pluck('id')->toArray();

        // Default IDs
        $defaultUserId = 22369; // Mandeep Singh
        $defaultCountryId = !empty($validCountryIds) ? min($validCountryIds) : 3;
        $defaultDepartmentId = !empty($validDepartmentIds) ? min($validDepartmentIds) : 4;
        $defaultHiredFromId = 144; // Direct

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
            $validHiredFromIds = [];
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
            // Log invalid DATETIME records
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

        // Update validHiredFromIds
        $validHiredFromIds = DB::connection('mysql')->table('hired_froms')->pluck('id')->toArray();
        $hiredFromMappings = DB::connection('mysql')->table('hired_froms')->pluck('id', 'name')->toArray();
        $acmHiredFromMappings = DB::connection('old_crm_db')->table('acm_hiredfrom')->pluck('name', 'id')->toArray();

        // Get existing project IDs
        $existingIds = DB::connection('mysql')->table('sale_team_projects')->pluck('id')->toArray();

        // Fetch projects
        $oldProjects = DB::connection('old_crm_db')
            ->table('acm_sale_projects')
            ->where('isDeleted', 'no')
            ->where(function ($query) {
                $query->whereNull('deleted_at')
                      ->orWhere('deleted_at', '0000-00-00 00:00:00');
            })
            ->get();

        $total = $oldProjects->count();
        $this->info("Found {$total} projects to import.");

        $insertedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $batch = [];
        $batchSize = 100;
        $invalidHiredFroms = [];
        $invalidUsers = [];

        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($oldProjects as $project) {
            // Skip if project already exists
            if (in_array($project->id, $existingIds)) {
                $this->info("Project ID {$project->id} already exists, skipping.");
                Log::info("Project ID {$project->id} skipped due to existing ID");
                $skippedCount++;
                continue;
            }

            // Skip if required fields are empty
            if (empty($project->project_name) || empty($project->client_name) || empty($project->client_email)) {
                $this->warn("Project ID {$project->id} has empty required fields, skipping.");
                Log::warning("Project ID {$project->id} has empty required fields", [
                    'project_name' => $project->project_name,
                    'client_name' => $project->client_name,
                    'client_email' => $project->client_email,
                ]);
                DB::connection('mysql')->table('skipped_sale_projects')->insert([
                    'project_id' => $project->id,
                    'project_name' => $project->project_name,
                    'error_message' => 'Missing required fields: project_name, client_name, or client_email',
                    'created_at' => now(),
                ]);
                $skippedCount++;
                continue;
            }

            // Format project_month
            $projectMonth = null;
            if (!empty($project->delivery_date) && $project->delivery_date !== '0000-00-00') {
                try {
                    $projectMonth = Carbon::parse($project->delivery_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    $this->warn("Invalid delivery_date for project ID {$project->id}: {$project->delivery_date}");
                    Log::warning("Invalid delivery_date for project ID {$project->id}", [
                        'delivery_date' => $project->delivery_date,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            if (!$projectMonth && $project->created_at && $project->created_at !== '0000-00-00 00:00:00') {
                $projectMonth = Carbon::parse($project->created_at)->format('Y-m-d');
            }

            // Map sales_person_id
            $salesPersonId = isset($userIdMap[$project->project_sale_person])
                ? $userIdMap[$project->project_sale_person]
                : $defaultUserId;
            if (!in_array($salesPersonId, $validUserIds)) {
                $this->warn("Invalid sales_person_id {$project->project_sale_person} for project ID {$project->id}, using default ID {$defaultUserId}");
                Log::warning("Invalid sales_person_id {$project->project_sale_person} for project ID {$project->id}", [
                    'old_sale_person' => $project->project_sale_person,
                    'default_id' => $defaultUserId,
                ]);
                $invalidUsers[$project->id] = $project->project_sale_person;
                $salesPersonId = $defaultUserId;
            }

            // Map hired_from_portal and hired_from_profile_id
            $hiredFromName = isset($project->project_hired_from) && isset($acmHiredFromMappings[$project->project_hired_from])
                ? $acmHiredFromMappings[$project->project_hired_from]
                : 'Direct';
            $hiredFromPortal = match (strtolower($hiredFromName)) {
                'pph', 'pph mandy', 'pph seodiscovery', 'pph seo developer', 'pph - content elites' => 'PPH',
                'upwork', 'upwork - seo developer', 'upwork seo discovery', 'upwork gurpreet', 'upwork - deleted - please select other option -' => 'Upwork',
                'fiver', 'fiverr seo developer', 'fiver - canadian', 'fiver - mandeep (main)' => 'Fiver',
                default => 'PPH',
            };
            $hiredFromProfileId = $defaultHiredFromId;
            if (isset($project->project_hired_from) && in_array($project->project_hired_from, $validHiredFromIds)) {
                $hiredFromProfileId = $project->project_hired_from;
            } else if ($project->project_hired_from) {
                $this->warn("Invalid project_hired_from {$project->project_hired_from} for project ID {$project->id}, using default ID {$defaultHiredFromId}");
                Log::warning("Invalid project_hired_from {$project->project_hired_from} for project ID {$project->id}", [
                    'hired_from_name' => $hiredFromName,
                    'default_id' => $defaultHiredFromId,
                ]);
                $invalidHiredFroms[$project->id] = $hiredFromName;
            }

            // Map project_type
            $projectType = match (strtolower($project->project_type ?? '')) {
                'fixed', 'one time' => 'One-time',
                default => 'Ongoing',
            };

            // Map client_type
            $clientType = match (strtolower($project->client_type ?? '')) {
                'new' => 'new client',
                default => 'old client',
            };

            // Map business_type
            $businessType = match (strtolower($project->business_type ?? '')) {
                'mid', 'midlevel' => 'Midlevel',
                'startup' => 'Startup',
                'small' => 'Small',
                default => 'Enterprise',
            };

            // Map website_speed_included
            $websiteSpeedIncluded = !empty($project->client_website_speed) && $project->client_website_speed !== 'None' ? 'Yes' : 'No';

            // Handle price_usd
            $priceUsd = (float) ($project->project_price ?? 0);
            if ($priceUsd > 99999999.99) {
                $this->warn("Project ID {$project->id}: price_usd {$priceUsd} exceeds decimal(10,2) limit, capping at 99999999.99");
                Log::warning("Price capped for project ID {$project->id}", ['price_usd' => $priceUsd]);
                $priceUsd = 99999999.99;
            }

            // Handle timestamps
            $createdAt = $project->created_at && $project->created_at !== '0000-00-00 00:00:00'
                ? Carbon::parse($project->created_at)->format('Y-m-d H:i:s')
                : now();
            $updatedAt = $project->updated_at && $project->updated_at !== '0000-00-00 00:00:00'
                ? Carbon::parse($project->updated_at)->format('Y-m-d H:i:s')
                : $createdAt;

            // Prepare data
            $data = [
                'id' => $project->id,
                'hired_from_portal' => $hiredFromPortal,
                'hired_from_profile_id' => $hiredFromProfileId,
                'name_or_url' => substr(trim($project->project_name ?? ''), 0, 255),
                'description' => $project->project_desc,
                'price_usd' => $priceUsd,
                'project_type' => $projectType,
                'client_type' => $clientType,
                'business_type' => $businessType,
                'project_month' => $projectMonth,
                'country_id' => in_array($project->country_id, $validCountryIds) ? $project->country_id : $defaultCountryId,
                'sales_person_id' => $salesPersonId,
                'department_id' => in_array($project->project_department, $validDepartmentIds) ? $project->project_department : $defaultDepartmentId,
                'client_name' => substr(trim($project->client_name ?? ''), 0, 255),
                'client_email' => substr(trim($project->client_email ?? ''), 0, 255),
                'time_to_contact' => $project->client_contact_time,
                'client_other_info' => $project->client_description,
                'client_behaviour' => $project->client_behaviour,
                'communication_details' => $project->client_communication,
                'specific_keywords' => $project->client_specific_keywords,
                'result_commitment' => $project->client_specific_commitment,
                'website_speed_included' => $websiteSpeedIncluded,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
                'any_website_development_commitment' => $project->client_websitework_commitment,
                'internal_loon_video' => $project->client_internal_loomvideo,
                'website_dev_commitment' => $project->client_websitework_commitment,
                'internal_explainer_video' => null,
                'content_commitment' => $project->client_content_commitment,
            ];

            // Validate required fields
            $requiredFields = ['hired_from_portal', 'hired_from_profile_id', 'name_or_url', 'price_usd', 'sales_person_id', 'department_id', 'country_id', 'client_name', 'client_email'];
            $canProceed = true;
            foreach ($requiredFields as $field) {
                if (is_null($data[$field])) {
                    $this->warn("Skipping project ID {$project->id}: {$field} is NULL");
                    Log::warning("Skipping project ID {$project->id}: {$field} is NULL", ['project' => (array)$project]);
                    DB::connection('mysql')->table('skipped_sale_projects')->insert([
                        'project_id' => $project->id,
                        'project_name' => $project->project_name,
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

            if (count($batch) >= $batchSize || $project === $oldProjects->last()) {
                try {
                    $values = array_map(function ($item) {
                        return '(' . implode(',', array_map(function ($value) {
                            return is_null($value) ? 'NULL' : DB::connection('mysql')->getPdo()->quote($value);
                        }, $item)) . ')';
                    }, $batch);
                    $columns = implode(',', array_keys($data));
                    $sql = "INSERT IGNORE INTO laravelcrm.sale_team_projects ($columns) VALUES " . implode(',', $values);
                    $affectedRows = DB::connection('mysql')->statement($sql);
                    $insertedCount += $affectedRows;
                    $this->info("Inserted batch of $affectedRows projects (batch size: " . count($batch) . ")");
                    if ($affectedRows < count($batch)) {
                        $skippedCount += (count($batch) - $affectedRows);
                        Log::warning("Some projects skipped in batch due to duplicates or constraints", [
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
                        DB::connection('mysql')->table('skipped_sale_projects')->insert([
                            'project_id' => $batchData['id'],
                            'project_name' => $batchData['name_or_url'],
                            'error_message' => $e->getMessage(),
                            'created_at' => now(),
                        ]);
                    }
                    $skippedCount += count($batch);
                    $errorCount += count($batch);
                }
                $batch = [];
            }
        }

        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');

        // Validate foreign keys
        $this->info('Validating foreign keys...');
        $invalidForeignKeys = DB::connection('mysql')
            ->table('sale_team_projects')
            ->whereNotIn('sales_person_id', $validUserIds)
            ->orWhereNotIn('country_id', $validCountryIds)
            ->orWhereNotIn('department_id', $validDepartmentIds)
            ->orWhereNotIn('hired_from_profile_id', $validHiredFromIds)
            ->pluck('id')
            ->toArray();
        if (!empty($invalidForeignKeys)) {
            $this->warn("Found " . count($invalidForeignKeys) . " projects with invalid foreign keys.");
            Log::warning("Projects with invalid foreign keys", ['ids' => $invalidForeignKeys]);
            DB::connection('mysql')
                ->table('sale_team_projects')
                ->whereIn('id', $invalidForeignKeys)
                ->update([
                    'sales_person_id' => $defaultUserId,
                    'country_id' => $defaultCountryId,
                    'department_id' => $defaultDepartmentId,
                    'hired_from_profile_id' => $defaultHiredFromId,
                    'updated_at' => now(),
                ]);
            $this->info("Updated " . count($invalidForeignKeys) . " projects with default foreign key IDs.");
        }

        // Log summary
        if (!empty($invalidHiredFroms)) {
            $this->warn("Found " . count($invalidHiredFroms) . " projects with invalid project_hired_from values.");
            Log::warning("Invalid project_hired_from values", ['hired_froms' => $invalidHiredFroms]);
        }
        if (!empty($invalidUsers)) {
            $this->warn("Found " . count($invalidUsers) . " projects with invalid sales_person_id values.");
            Log::warning("Invalid sales_person_id values", ['users' => $invalidUsers]);
        }

        $totalRecords = DB::connection('mysql')->table('sale_team_projects')->count();
        $newRecords = DB::connection('mysql')
            ->table('sale_team_projects')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $this->info("✅ Import completed. Inserted: $insertedCount, Skipped: $skippedCount, Failed: $errorCount");
        $this->info("Total records in sale_team_projects: $totalRecords");
        $this->info("New records inserted in this run (last 24 hours): $newRecords");
        $this->info("Unmatched users: " . count($invalidUsers));
        return $errorCount > 0 ? 1 : 0;
    }
}