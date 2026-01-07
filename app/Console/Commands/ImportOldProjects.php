<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportOldProjects extends Command
{
    protected $signature = 'import:old-projects {--debug : Enable debug mode}';
    protected $description = 'Import projects from old_crm_db.acm_projects to laravelcrm.projects and assigned_projects, including secondary users, mapping Pause to Paused';

    public function handle()
    {
        $startTime = microtime(true);
        $debugMode = $this->option('debug');
        $this->info('🔁 Starting projects import into laravelcrm.projects and assigned_projects... Debug mode: ' . ($debugMode ? 'ON' : 'OFF'));

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
        $requiredTables = ['projects', 'users', 'departments', 'project_categories', 'assigned_projects'];
        foreach ($requiredTables as $table) {
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable($table)) {
                $this->error("$table table does not exist in laravelcrm.");
                Log::error("$table table not found");
                return 1;
            }
        }

        // Check task_phases table
        $hasTaskPhasesTable = DB::connection('mysql')->getSchemaBuilder()->hasTable('task_phases');
        $nameColumn = null;
        if ($hasTaskPhasesTable) {
            $taskPhaseColumns = DB::connection('mysql')->getSchemaBuilder()->getColumnListing('task_phases');
            $hasNameColumn = in_array('name', $taskPhaseColumns) || in_array('phase_name', $taskPhaseColumns);
            $nameColumn = in_array('name', $taskPhaseColumns) ? 'name' : (in_array('phase_name', $taskPhaseColumns) ? 'phase_name' : null);
            if (!$hasNameColumn) {
                $this->warn('task_phases table lacks name/phase_name column. Skipping name insertion for task_phases.');
                Log::warning('task_phases table lacks name/phase_name column');
            }
        } else {
            $this->warn('task_phases table not found in laravelcrm. Setting task_phases to null.');
            Log::warning('task_phases table not found');
        }

        // Check acm_project_assigned_phases table
        $hasOldTaskPhasesTable = DB::connection('old_crm_db')->getSchemaBuilder()->hasTable('acm_project_assigned_phases');
        if (!$hasOldTaskPhasesTable) {
            $this->warn('acm_project_assigned_phases table not found in old_crm_db. task_phases will be null.');
            Log::warning('acm_project_assigned_phases table not found');
        }

        // Check acm_project_secondary_users table
        $hasSecondaryUsersTable = DB::connection('old_crm_db')->getSchemaBuilder()->hasTable('acm_project_secondary_users');
        if (!$hasSecondaryUsersTable) {
            $this->warn('acm_project_secondary_users table not found in old_crm_db. additional_employees will be null.');
            Log::warning('acm_project_secondary_users table not found');
        } else {
            $this->info('✅ Found acm_project_secondary_users table.');
        }

        // Check skipped_projects table
        $hasSkippedProjectsTable = DB::connection('mysql')->getSchemaBuilder()->hasTable('skipped_projects');
        if (!$hasSkippedProjectsTable) {
            $this->warn('skipped_projects table not found. Logging to laravel.log.');
        }

        // Map user IDs
        $this->info('Mapping user IDs from old_crm_db.acm_users to laravelcrm.users...');
        $oldUsers = DB::connection('old_crm_db')->table('acm_users')->select('id', 'email', 'name')->get();
        $newUsers = DB::connection('mysql')->table('users')->select('id', 'email', 'name')->get();
        $userIdMap = [];
        $unmatchedUsers = [];

        foreach ($oldUsers as $oldUser) {
            $matched = false;
            // Match by email (primary)
            foreach ($newUsers as $newUser) {
                if (strtolower($oldUser->email) === strtolower($newUser->email)) {
                    $userIdMap[$oldUser->id] = $newUser->id;
                    $matched = true;
                    break;
                }
            }
            // Match by name (fallback)
            if (!$matched) {
                foreach ($newUsers as $newUser) {
                    if (strtolower($oldUser->name) === strtolower($newUser->name)) {
                        $userIdMap[$oldUser->id] = $newUser->id;
                        $matched = true;
                        break;
                    }
                }
            }
            if (!$matched && $debugMode) {
                $unmatchedUsers[$oldUser->id] = ['email' => $oldUser->email, 'name' => $oldUser->name];
            }
        }
        $this->info('Mapped ' . count($userIdMap) . ' users. Unmatched users: ' . count($unmatchedUsers));
        Log::info('User ID mapping', ['mapped' => count($userIdMap), 'unmatched' => count($unmatchedUsers)]);

        // Get valid IDs
        $validUserIds = DB::connection('mysql')->table('users')->pluck('id')->toArray();
        $defaultUserId = !empty($validUserIds) ? min($validUserIds) : null;
        if (!$defaultUserId) {
            $this->error('No valid user IDs found in laravelcrm.users.');
            Log::error('No valid user IDs found');
            return 1;
        }
        $this->info("Default user ID: $defaultUserId");

        // Fetch Team Lead user IDs (role_id = 3)
        $teamLeadIds = DB::connection('old_crm_db')
            ->table('acm_user_roles')
            ->where('role_id', 3)
            ->pluck('user_id')
            ->toArray();
        $this->info('Found ' . count($teamLeadIds) . ' Team Lead users in old_crm_db.acm_user_roles.');

        $validDepartmentIds = DB::connection('mysql')->table('departments')->pluck('id')->toArray();
        $defaultDepartmentId = !empty($validDepartmentIds) ? min($validDepartmentIds) : null;
        if (!$defaultDepartmentId) {
            $this->error('No valid department IDs found in laravelcrm.departments.');
            Log::error('No valid department IDs found');
            return 1;
        }

        $validCategoryIds = DB::connection('mysql')->table('project_categories')->pluck('id')->toArray();
        $defaultCategoryId = !empty($validCategoryIds) ? min($validCategoryIds) : null;

        $validPhaseIds = $hasTaskPhasesTable ? DB::connection('mysql')->table('task_phases')->pluck('id')->toArray() : [];
        $defaultPhaseId = !empty($validPhaseIds) ? min($validPhaseIds) : null;

        // Fetch task phases
        $taskPhases = [];
        if ($hasTaskPhasesTable && $hasOldTaskPhasesTable) {
            $this->info('Fetching task phases from old_crm_db.acm_project_assigned_phases...');
            $taskPhases = DB::connection('old_crm_db')
                ->table('acm_project_assigned_phases')
                ->select('project_id', DB::raw('GROUP_CONCAT(phase_id) as phase_ids'))
                ->groupBy('project_id')
                ->pluck('phase_ids', 'project_id')
                ->map(function ($phaseIds) use ($validPhaseIds, $defaultPhaseId) {
                    $ids = explode(',', $phaseIds);
                    $mappedIds = array_filter(array_map(function ($id) use ($validPhaseIds, $defaultPhaseId) {
                        return in_array($id, $validPhaseIds) ? $id : null;
                    }, $ids));
                    return !empty($mappedIds) ? $mappedIds : ($defaultPhaseId ? [$defaultPhaseId] : []);
                })
                ->toArray();
            $this->info('Fetched task phases for ' . count($taskPhases) . ' projects.');
        }

        // Fetch secondary users
        $secondaryUsers = [];
        $invalidSecondaryUsers = [];
        if ($hasSecondaryUsersTable) {
            $this->info('Fetching secondary users from old_crm_db.acm_project_secondary_users...');
            $secondaryUsers = DB::connection('old_crm_db')
                ->table('acm_project_secondary_users')
                ->select('project_id', DB::raw('GROUP_CONCAT(user_id) as user_ids'))
                ->groupBy('project_id')
                ->pluck('user_ids', 'project_id')
                ->map(function ($userIds) use ($userIdMap, $validUserIds, $debugMode, &$invalidSecondaryUsers) {
                    $ids = explode(',', $userIds);
                    $mappedIds = [];
                    foreach ($ids as $id) {
                        if (isset($userIdMap[$id]) && in_array($userIdMap[$id], $validUserIds)) {
                            $mappedIds[] = $userIdMap[$id];
                        } else {
                            $invalidSecondaryUsers[] = ['user_id' => $id, 'mapped_id' => isset($userIdMap[$id]) ? $userIdMap[$id] : null];
                            if ($debugMode) {
                                $this->warn("Invalid secondary user ID $id, skipping for additional_employees");
                                Log::warning("Invalid secondary user ID $id, skipping", ['user_id' => $id, 'mapped_id' => isset($userIdMap[$id]) ? $userIdMap[$id] : null]);
                            }
                        }
                    }
                    $mappedIds = array_unique(array_filter($mappedIds));
                    return !empty($mappedIds) ? $mappedIds : null;
                })
                ->filter()
                ->toArray();
            $this->info('Fetched secondary users for ' . count($secondaryUsers) . ' projects.');
            if (!empty($invalidSecondaryUsers)) {
                $this->warn('Found ' . count($invalidSecondaryUsers) . ' invalid secondary user IDs.');
                Log::warning('Invalid secondary user IDs', ['invalid_users' => $invalidSecondaryUsers]);
            }
        }

        // Import missing department IDs
        $this->info('Importing missing department IDs into laravelcrm.departments...');
        DB::connection('mysql')->statement("
            INSERT IGNORE INTO laravelcrm.departments (id, name, created_at, updated_at)
            SELECT DISTINCT project_department, CONCAT('Department ', project_department), NOW(), NOW()
            FROM old_crm_db.acm_projects
            WHERE isDeleted = 'no' AND project_department IS NOT NULL
            AND project_department NOT IN (SELECT id FROM laravelcrm.departments)
        ");
        $newDepartments = DB::connection('mysql')->table('departments')->where('created_at', '>=', now()->subMinutes(5))->count();
        $this->info("Imported $newDepartments new departments.");

        // Import missing task phase IDs
        if ($hasTaskPhasesTable && $hasOldTaskPhasesTable) {
            $this->info('Importing missing task phase IDs into laravelcrm.task_phases...');
            try {
                $columns = $nameColumn ? "id, $nameColumn, created_at, updated_at" : 'id, created_at, updated_at';
                $values = $nameColumn ? "phase_id, CONCAT('Phase ', phase_id), NOW(), NOW()" : 'phase_id, NOW(), NOW()';
                DB::connection('mysql')->statement("
                    INSERT IGNORE INTO laravelcrm.task_phases ($columns)
                    SELECT DISTINCT $values
                    FROM old_crm_db.acm_project_assigned_phases
                    WHERE phase_id NOT IN (SELECT id FROM laravelcrm.task_phases)
                ");
                $newPhases = DB::connection('mysql')->table('task_phases')->where('created_at', '>=', now()->subMinutes(5))->count();
                $this->info("Imported $newPhases new task phases.");
            } catch (\Exception $e) {
                $this->warn("Failed to import task phases: {$e->getMessage()}");
                Log::warning("Failed to import task phases", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        }

        // Fetch all projects
        $this->info('Fetching projects from old_crm_db.acm_projects...');
        $oldProjects = DB::connection('old_crm_db')
            ->table('acm_projects')
            ->where('isDeleted', 'no')
            ->get();
        $this->info('Found ' . count($oldProjects) . ' projects.');

        $insertedCount = 0;
        $skippedCount = 0;
        $batch = [];
        $assignedBatch = [];
        $duplicateIds = [];
        $invalidUserIds = [];
        $skippedProjects = [];

        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($oldProjects as $index => $project) {
            if ($debugMode) {
                $this->info("Processing Project ID {$project->id}: project_status={$project->project_status}, project_manager={$project->project_manager}, project_employee={$project->project_employee}, team_lead={$project->team_lead}");
            }

            if (in_array($project->id, $duplicateIds)) {
                $errorMessage = "Skipping project ID {$project->id}: duplicate ID in source data";
                $this->error($errorMessage);
                Log::error($errorMessage, ['project' => $project->id]);
                $skippedProjects[] = ['id' => $project->id, 'reason' => 'Duplicate ID'];
                $skippedCount++;
                continue;
            }
            $duplicateIds[] = $project->id;

            // Map fields
            $projectType = in_array($project->project_type, ['Ongoing', 'One Time', 'Monthly']) ? str_replace('One Time', 'One-time', $project->project_type) : 'One-time';
            $clientType = match (strtolower($project->client_type ?? '')) {
                'new' => 'New Client',
                default => 'Old Client',
            };
            $businessType = match (strtolower($project->business_type ?? '')) {
                'startup' => 'Startup',
                'small' => 'Small',
                'mid', 'midlevel', 'mid-level' => 'Mid-level',
                default => 'Enterprise',
            };
            $upworkProjectType = match (strtolower($project->upwork_project_type ?? '')) {
                'hourly' => 'Hourly',
                'fixed' => 'Fixed',
                default => null,
            };
            $reportType = match (strtolower($project->report_type ?? '')) {
                'weekly' => 'Weekly',
                'bi-weekly' => 'Bi-Weekly',
                'monthly' => 'Monthly',
                default => 'Weekly',
            };
            $price = number_format((float) ($project->project_price ?? 0), 2, '.', '');
            $clientEmail = !empty($project->client_email) && $project->client_email !== 'NA' ? substr(trim(explode('/', $project->client_email)[0]), 0, 255) : null;
            $nameOrUrl = !empty($project->project_name) && !in_array($project->project_name, ['WEB I', 'Youtube Paid Ads | Pankaj', 'Dorian'])
                ? substr($project->project_name, 0, 255)
                : (!empty($project->dashboard_url) ? substr($project->dashboard_url, 0, 255) : 'Project_' . $project->id);

            // Validate user IDs
            $projectManagerId = !empty($project->project_manager) && isset($userIdMap[$project->project_manager])
                ? $userIdMap[$project->project_manager]
                : $defaultUserId;
            $assignMainEmployeeId = !empty($project->project_employee) && isset($userIdMap[$project->project_employee])
                ? $userIdMap[$project->project_employee]
                : $defaultUserId;
            $teamLeadId = !empty($project->team_lead) && isset($userIdMap[$project->team_lead]) && in_array($project->team_lead, $teamLeadIds)
                ? $userIdMap[$project->team_lead]
                : $defaultUserId;
            $salesPersonId = !empty($project->project_sale_person) && isset($userIdMap[$project->project_sale_person])
                ? $userIdMap[$project->project_sale_person]
                : $defaultUserId;
            $createdBy = !empty($project->created_by_user_id) && isset($userIdMap[$project->created_by_user_id])
                ? $userIdMap[$project->created_by_user_id]
                : $defaultUserId;
            $upsellEmpId = !empty($project->upsell_employee) && isset($userIdMap[$project->upsell_employee])
                ? $userIdMap[$project->upsell_employee]
                : $defaultUserId;
            $contentManagerId = !empty($project->content_user_id) && isset($userIdMap[$project->content_user_id])
                ? $userIdMap[$project->content_user_id]
                : $defaultUserId;

            // Log invalid team_lead_id
            if ($project->team_lead && (!isset($userIdMap[$project->team_lead]) || !in_array($project->team_lead, $teamLeadIds))) {
                $this->warn("Invalid or non-Team Lead ID {$project->team_lead} for project ID {$project->id}, using default ID {$defaultUserId}");
                Log::warning("Invalid or non-Team Lead ID {$project->team_lead} for project ID {$project->id}", ['team_lead' => $project->team_lead]);
            }

            // Map secondary users
            $additionalEmployees = isset($secondaryUsers[$project->id]) ? json_encode($secondaryUsers[$project->id]) : null;
            if ($debugMode && isset($secondaryUsers[$project->id])) {
                $this->info("Mapped secondary users for project ID {$project->id}: " . json_encode($secondaryUsers[$project->id]));
                Log::info("Mapped secondary users for project ID {$project->id}", ['additional_employees' => $secondaryUsers[$project->id]]);
            }

            // Log invalid user IDs
            $invalidFields = [];
            if ($project->project_manager && (!isset($userIdMap[$project->project_manager]) || !in_array($userIdMap[$project->project_manager], $validUserIds))) {
                $invalidFields[] = "project_manager: {$project->project_manager}";
            }
            if ($project->project_employee && (!isset($userIdMap[$project->project_employee]) || !in_array($userIdMap[$project->project_employee], $validUserIds))) {
                $invalidFields[] = "project_employee: {$project->project_employee}";
            }
            if ($project->team_lead && (!isset($userIdMap[$project->team_lead]) || !in_array($userIdMap[$project->team_lead], $validUserIds))) {
                $invalidFields[] = "team_lead: {$project->team_lead}";
            }
            if ($project->project_sale_person && (!isset($userIdMap[$project->project_sale_person]) || !in_array($userIdMap[$project->project_sale_person], $validUserIds))) {
                $invalidFields[] = "sales_person: {$project->project_sale_person}";
            }
            if ($project->created_by_user_id && (!isset($userIdMap[$project->created_by_user_id]) || !in_array($userIdMap[$project->created_by_user_id], $validUserIds))) {
                $invalidFields[] = "created_by: {$project->created_by_user_id}";
            }
            if ($project->upsell_employee && (!isset($userIdMap[$project->upsell_employee]) || !in_array($userIdMap[$project->upsell_employee], $validUserIds))) {
                $invalidFields[] = "upsell_employee: {$project->upsell_employee}";
            }
            if ($project->content_user_id && (!isset($userIdMap[$project->content_user_id]) || !in_array($userIdMap[$project->content_user_id], $validUserIds))) {
                $invalidFields[] = "content_manager: {$project->content_user_id}";
            }
            if (isset($secondaryUsers[$project->id])) {
                $originalUserIds = explode(',', $userIds[$project->id] ?? '');
                foreach ($originalUserIds as $userId) {
                    if (!isset($userIdMap[$userId]) || !in_array($userIdMap[$userId], $validUserIds)) {
                        $invalidFields[] = "secondary_user: $userId";
                    }
                }
            }
            if (!empty($invalidFields)) {
                $invalidUserIds[$project->id] = $invalidFields;
            }

            $categoryId = !empty($project->project_cats) && in_array($project->project_cats, $validCategoryIds)
                ? $project->project_cats
                : $defaultCategoryId;

            $departmentId = !empty($project->project_department) && in_array($project->project_department, $validDepartmentIds)
                ? $project->project_department
                : $defaultDepartmentId;

            $projectMonth = null;
            if (!empty($project->delivery_date) && $project->delivery_date !== '0000-00-00') {
                try {
                    $projectMonth = Carbon::parse($project->delivery_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    $this->warn("Invalid delivery_date for project ID {$project->id}: {$project->delivery_date}");
                    Log::warning("Invalid delivery_date for project ID {$project->id}", ['date' => $project->delivery_date]);
                }
            }

            $createdAt = $project->created_at && $project->created_at !== '0000-00-00 00:00:00'
                ? Carbon::parse($project->created_at)->format('Y-m-d H:i:s')
                : null;
            $updatedAt = $project->updated_at && $project->updated_at !== '0000-00-00 00:00:00'
                ? Carbon::parse($project->updated_at)->format('Y-m-d H:i:s')
                : $createdAt;

            $taskPhasesJson = ($hasTaskPhasesTable && !empty($taskPhases[$project->id])) ? json_encode($taskPhases[$project->id]) : null;

            $contentDetails = [
                'price' => $project->content_price ?? 0,
                'type' => $project->content_type ?? null,
                'quantity' => $project->content_quantity ?? null,
                'specific_keywords' => $project->client_specific_keywords ?? '',
                'specific_commitment' => $project->client_specific_commitment ?? '',
                'content_commitment' => $project->client_content_commitment ?? '',
                'websitework_commitment' => $project->client_websitework_commitment ?? '',
            ];

            // Map project_status
            $projectStatus = match ($project->project_status) {
                'Pause' => 'Paused',
                'Issue' => 'Issues',
                'TEMP HOLD' => 'Temp Hold',
                'Complete', 'Hold', 'Working', 'Issues', 'Temp Hold' => $project->project_status,
                default => 'Working',
            };

            if ($debugMode && $project->project_status === 'Pause') {
                $this->info("Mapped project_status 'Pause' to 'Paused' for project ID {$project->id}");
                Log::info("Mapped project_status 'Pause' to 'Paused'", ['project_id' => $project->id]);
            }

            $data = [
                'id' => $project->id,
                'sale_team_project_id' => $project->id,
                'project_category_id' => $categoryId,
                'project_subcategory_id' => $project->project_sub_cats ?? null,
                'name_or_url' => $nameOrUrl,
                'dashboard_url' => $project->dashboard_url ? substr($project->dashboard_url, 0, 255) : null,
                'description' => $project->project_desc ?? null,
                'project_grade' => in_array($project->project_grade, ['A', 'AA', 'AAA']) ? $project->project_grade : null,
                'business_type' => $businessType,
                'country_id' => $project->country_id ?? null,
                'task_phases' => $taskPhasesJson,
                'project_manager_id' => $projectManagerId,
                'assign_main_employee_id' => $assignMainEmployeeId,
                'price' => $price,
                'estimated_hours' => $project->project_hours ?? null,
                'project_type' => $projectType,
                'upwork_project_type' => $upworkProjectType,
                'client_type' => $clientType,
                'report_type' => $reportType,
                'project_month' => $projectMonth,
                'sales_person_id' => $salesPersonId,
                'department_id' => $departmentId,
                'client_name' => !empty($project->client_name) ? substr($project->client_name, 0, 255) : null,
                'client_email' => $clientEmail,
                'client_other_info' => $project->client_description ?? null,
                'additional_employees' => $additionalEmployees,
                'created_by' => $createdBy,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
                'project_status' => $projectStatus,
                'status_date' => $project->status_update_date ?? null,
                'reason_description' => $project->reason_description ?? null,
                'can_client_rehire' => in_array($project->client_can_rehire, ['Yes', 'No']) ? $project->client_can_rehire : 'No',
                'rehire_date' => $project->rehire_date ?? null,
                'closed_by' => $project->closed_by ?? null,
                'team_lead_id' => $teamLeadId,
                'upsell_employee_id' => $upsellEmpId,
                'content_manager_id' => $contentManagerId,
                'content_details' => json_encode($contentDetails),
            ];

            if (empty($data['name_or_url'])) {
                $errorMessage = "Skipping project ID {$project->id}: name_or_url is empty";
                $this->error($errorMessage);
                Log::error($errorMessage, ['project' => $project->id, 'data' => $data]);
                $skippedProjects[] = ['id' => $project->id, 'reason' => 'Empty name_or_url'];
                if ($hasSkippedProjectsTable) {
                    DB::connection('mysql')->table('skipped_projects')->insert([
                        'id' => $project->id,
                        'project_name' => $project->project_name ?? $project->dashboard_url ?? 'Unknown',
                        'error_message' => $errorMessage,
                        'created_at' => now(),
                    ]);
                }
                $skippedCount++;
                continue;
            }

            $batch[] = $data;

            $assignedData = [
                'project_id' => $project->id,
                'project_manager_id' => $projectManagerId,
                'team_lead_id' => $teamLeadId,
                'assigned_employee_id' => $assignMainEmployeeId,
                'hour' => $project->project_hours ?? null,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];
            $assignedBatch[] = $assignedData;

            if (count($batch) >= 100 || $index == count($oldProjects) - 1) {
                try {
                    $values = array_map(function ($item) {
                        return '(' . implode(',', array_map(function ($value) {
                            return is_null($value) ? 'NULL' : DB::connection('mysql')->getPdo()->quote($value);
                        }, $item)) . ')';
                    }, $batch);
                    $columns = implode(',', array_keys($batch[0]));
                    $sql = "INSERT IGNORE INTO laravelcrm.projects ($columns) VALUES " . implode(',', $values);
                    $affectedRows = DB::connection('mysql')->statement($sql);
                    $insertedCount += $affectedRows;
                    $this->info("Inserted batch of $affectedRows projects into laravelcrm.projects (batch size: " . count($batch) . ")");
                    if ($affectedRows < count($batch)) {
                        Log::warning("Some projects skipped in batch due to duplicates", [
                            'batch_size' => count($batch),
                            'inserted' => $affectedRows,
                        ]);
                        $skippedCount += (count($batch) - $affectedRows);
                        $skippedProjects[] = ['id' => null, 'reason' => 'Duplicate in batch'];
                    }
                } catch (\Exception $e) {
                    $this->error("Batch insertion failed: {$e->getMessage()}");
                    Log::error("Batch insertion failed: {$e->getMessage()}", [
                        'batch_size' => count($batch),
                        'sql' => $sql ?? 'N/A',
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $skippedCount += count($batch);
                    $skippedProjects[] = ['id' => null, 'reason' => 'Batch insertion error: ' . $e->getMessage()];
                }
                $batch = [];
            }

            if (count($assignedBatch) >= 100 || $index == count($oldProjects) - 1) {
                try {
                    $values = array_map(function ($item) {
                        return '(' . implode(',', array_map(function ($value) {
                            return is_null($value) ? 'NULL' : DB::connection('mysql')->getPdo()->quote($value);
                        }, $item)) . ')';
                    }, $assignedBatch);
                    $columns = implode(',', array_keys($assignedBatch[0]));
                    $sql = "INSERT IGNORE INTO laravelcrm.assigned_projects ($columns) VALUES " . implode(',', $values);
                    $affectedRows = DB::connection('mysql')->statement($sql);
                    $this->info("Inserted batch of $affectedRows records into laravelcrm.assigned_projects (batch size: " . count($assignedBatch) . ")");
                    if ($affectedRows < count($assignedBatch)) {
                        Log::warning("Some assigned_projects skipped in batch due to duplicates", [
                            'batch_size' => count($assignedBatch),
                            'inserted' => $affectedRows,
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->error("Assigned projects batch insertion failed: {$e->getMessage()}");
                    Log::error("Assigned projects batch insertion failed: {$e->getMessage()}", [
                        'batch_size' => count($assignedBatch),
                        'sql' => $sql ?? 'N/A',
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
                $assignedBatch = [];
            }
        }

        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');

        // Validate project_manager_id, assign_main_employee_id, and team_lead_id
        $this->info('Validating project_manager_id, assign_main_employee_id, and team_lead_id...');
        $invalidAssignments = DB::connection('mysql')
            ->table('projects')
            ->whereNotIn('project_manager_id', $validUserIds)
            ->orWhereNotIn('assign_main_employee_id', $validUserIds)
            ->orWhereNotIn('team_lead_id', $validUserIds)
            ->pluck('id')
            ->toArray();
        if (!empty($invalidAssignments)) {
            $this->warn("Projects with invalid project_manager_id, assign_main_employee_id, or team_lead_id: " . implode(', ', $invalidAssignments));
            Log::warning("Projects with invalid project_manager_id, assign_main_employee_id, or team_lead_id", ['ids' => $invalidAssignments]);
            DB::connection('mysql')
                ->table('projects')
                ->whereIn('id', $invalidAssignments)
                ->update([
                    'project_manager_id' => $defaultUserId,
                    'assign_main_employee_id' => $defaultUserId,
                    'team_lead_id' => $defaultUserId,
                    'updated_at' => now(),
                ]);
            $this->info("Updated invalid project_manager_id, assign_main_employee_id, and team_lead_id to $defaultUserId for " . count($invalidAssignments) . " projects.");
        }

        // Validate additional_employees
        $this->info('Validating additional_employees...');
        $invalidAdditionalEmployees = DB::connection('mysql')
            ->table('projects')
            ->whereNotNull('additional_employees')
            ->get()
            ->filter(function ($project) use ($validUserIds) {
                $employees = json_decode($project->additional_employees, true);
                return is_array($employees) && count(array_diff($employees, $validUserIds)) > 0;
            })
            ->pluck('id')
            ->toArray();
        if (!empty($invalidAdditionalEmployees)) {
            $this->warn("Projects with invalid additional_employees: " . implode(', ', $invalidAdditionalEmployees));
            Log::warning("Projects with invalid additional_employees", ['ids' => $invalidAdditionalEmployees]);
            DB::connection('mysql')
                ->table('projects')
                ->whereIn('id', $invalidAdditionalEmployees)
                ->update([
                    'additional_employees' => null,
                    'updated_at' => now(),
                ]);
            $this->info("Cleared invalid additional_employees for " . count($invalidAdditionalEmployees) . " projects.");
        }

        // Ensure assigned_projects entries exist
        $missingAssigned = DB::connection('mysql')
            ->table('projects')
            ->leftJoin('assigned_projects', 'projects.id', '=', 'assigned_projects.project_id')
            ->whereNull('assigned_projects.project_id')
            ->whereIn('projects.id', $oldProjects->pluck('id')->toArray())
            ->pluck('projects.id')
            ->toArray();
        if (!empty($missingAssigned)) {
            $this->warn("Projects missing in assigned_projects: " . implode(', ', $missingAssigned));
            Log::warning("Projects missing in assigned_projects", ['ids' => $missingAssigned]);
            foreach ($missingAssigned as $projectId) {
                try {
                    DB::connection('mysql')->table('assigned_projects')->insert([
                        'project_id' => $projectId,
                        'project_manager_id' => $defaultUserId,
                        'team_lead_id' => $defaultUserId,
                        'assigned_employee_id' => $defaultUserId,
                        'hour' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->info("Added missing assigned_projects entry for project ID $projectId");
                } catch (\Exception $e) {
                    $this->error("Failed to add assigned_projects for project ID $projectId: {$e->getMessage()}");
                    Log::error("Failed to add assigned_projects for project ID $projectId", ['error' => $e->getMessage()]);
                }
            }
        }

        // Log invalid user IDs and skipped projects
        if (!empty($invalidUserIds)) {
            $this->warn("Projects with invalid user IDs in source data: " . count($invalidUserIds));
            Log::warning("Invalid user IDs in source data", ['projects' => $invalidUserIds]);
        }
        if (!empty($skippedProjects)) {
            $this->warn("Skipped projects: " . count($skippedProjects));
            Log::warning("Skipped projects", ['projects' => $skippedProjects]);
            if ($hasSkippedProjectsTable) {
                foreach ($skippedProjects as $skipped) {
                    DB::connection('mysql')->table('skipped_projects')->insert([
                        'id' => $skipped['id'] ?? null,
                        'project_name' => $skipped['id'] ? "Project {$skipped['id']}" : 'Unknown',
                        'error_message' => $skipped['reason'],
                        'created_at' => now(),
                    ]);
                }
            }
        }

        // Log summary of additional_employees
        $additionalEmployeesCount = DB::connection('mysql')
            ->table('projects')
            ->whereNotNull('additional_employees')
            ->count();
        $this->info("Projects with additional_employees: $additionalEmployeesCount");
        Log::info("Projects with additional_employees", ['count' => $additionalEmployeesCount]);

        $totalRecords = DB::connection('mysql')->table('projects')->count();
        $newRecords = DB::connection('mysql')
            ->table('projects')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();
        $assignedRecords = DB::connection('mysql')->table('assigned_projects')->count();
        $this->info("Total records in projects: $totalRecords");
        $this->info("New records inserted in this run (last 5 minutes): $newRecords");
        $this->info("Total records in assigned_projects: $assignedRecords");
        $this->info("✅ Import completed. Inserted: $insertedCount | Skipped: $skippedCount");
        $this->info("Unmatched users: " . count($unmatchedUsers));
        $this->info("Invalid secondary users: " . count($invalidSecondaryUsers));
        $this->info("Log in with user ID $defaultUserId or set allow_all_projects = 1 to view projects.");

        $elapsed = microtime(true) - $startTime;
        $this->info("Total time: " . round($elapsed / 60, 2) . " minutes");
        return 0;
    }
}