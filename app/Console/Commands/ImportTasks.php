<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportTasks extends Command
{
    protected $signature = 'app:import-tasks';
    protected $description = 'Import tasks and their assignments from old CRM database to the new CRM database';

    public function handle()
    {
        $this->info('🔁 Starting task import...');

        // Step 1: Get mapping of old user IDs to new user IDs
        $this->info('🔍 Fetching user ID mappings...');
        $oldToNewUserIdMap = $this->getUserIdMapping();
        if (empty($oldToNewUserIdMap)) {
            $this->error('⚠️ No user ID mappings found. Please run the app:import-users command first.');
            Log::error('No user ID mappings found. Please run the app:import-users command first.');
            return 1;
        }
        $this->info('✅ Found ' . count($oldToNewUserIdMap) . ' user ID mappings.');
        Log::info('User ID mappings: ' . json_encode($oldToNewUserIdMap));

        // Step 2: Import tasks from acm_tasks
        $this->info('📋 Importing tasks from acm_tasks...');
        $oldTasks = DB::connection('old_crm_db')
            ->table('acm_tasks')
            ->where('isDeleted', 'no')
            ->orderBy('id')
            ->get();

        if ($oldTasks->isEmpty()) {
            $this->warn('⚠️ No tasks found in acm_tasks table.');
            Log::warning('No tasks found in acm_tasks table.');
        } else {
            $this->info('🔍 Found ' . $oldTasks->count() . ' tasks to process.');
        }

        $oldToNewTaskIdMap = [];
        foreach ($oldTasks as $oldTask) {
            // Skip if created_by_user_id doesn't exist in the new database
            $newCreatedById = $oldToNewUserIdMap[$oldTask->created_by_user_id] ?? null;
            if (!$newCreatedById) {
                $this->warn("⚠️ Skipping task ID {$oldTask->id}: Invalid created_by_user_id {$oldTask->created_by_user_id}");
                Log::warning("Skipping task ID {$oldTask->id}: Invalid created_by_user_id {$oldTask->created_by_user_id}");
                continue;
            }

            // Insert or update task in the new tasks table
            try {
                // Relaxed condition to avoid skipping tasks with same name
                $task = Task::updateOrCreate(
                    [
                        'id' => $oldTask->id, // Use old ID to preserve all tasks
                    ],
                    [
                        'name' => $oldTask->task_name,
                        'description' => $oldTask->task_description,
                        'created_by' => $newCreatedById,
                        'created_at' => $this->formatDate($oldTask->created_at),
                        'updated_at' => $this->formatDate($oldTask->updated_at),
                    ]
                );

                // Save task ID mapping
                $oldToNewTaskIdMap[$oldTask->id] = $task->id;

                $this->line("✅ Imported task: {$task->name} (Old ID: {$oldTask->id} → New ID: {$task->id})");
            } catch (\Exception $e) {
                $this->error("❌ Failed to import task ID {$oldTask->id}: {$e->getMessage()}");
                Log::error("Failed to import task ID {$oldTask->id}: {$e->getMessage()}");
            }
        }

        $this->info('✅ Imported ' . count($oldToNewTaskIdMap) . ' tasks.');
        Log::info('Task ID mappings: ' . json_encode($oldToNewTaskIdMap));

        // Step 3: Import task assignments from acm_task_users
        $this->info('📌 Importing task assignments from acm_task_users...');
        $oldTaskAssignments = DB::connection('old_crm_db')
            ->table('acm_task_users')
            ->orderBy('id')
            ->get();

        if ($oldTaskAssignments->isEmpty()) {
            $this->warn('⚠️ No task assignments found in acm_task_users table.');
            Log::warning('No task assignments found in acm_task_users table.');
        } else {
            $this->info('🔍 Found ' . $oldTaskAssignments->count() . ' task assignments to process.');
        }

        foreach ($oldTaskAssignments as $assignment) {
            // Log assignment details for debugging
            $this->line("Processing assignment ID {$assignment->id}: task_id={$assignment->task_id}, user_id={$assignment->user_id}, days=[m:{$assignment->monday},t:{$assignment->tuesday},w:{$assignment->wednesday},th:{$assignment->thursday},f:{$assignment->friday},sa:{$assignment->saturday},su:{$assignment->sunday}]");

            // Skip if task_id or user_id doesn't exist in the new database
            $newTaskId = $oldToNewTaskIdMap[$assignment->task_id] ?? null;
            $newUserId = $oldToNewUserIdMap[$assignment->user_id] ?? null;

            if (!$newTaskId || !$newUserId) {
                $this->warn("⚠️ Skipping assignment ID {$assignment->id}: Invalid task_id {$assignment->task_id} or user_id {$assignment->user_id}");
                Log::warning("Skipping assignment ID {$assignment->id}: Invalid task_id {$assignment->task_id} or user_id {$assignment->user_id}");
                continue;
            }

            // Convert boolean day fields to JSON array
            $days = [];
            if ($assignment->monday) $days[] = 'Monday';
            if ($assignment->tuesday) $days[] = 'Tuesday';
            if ($assignment->wednesday) $days[] = 'Wednesday';
            if ($assignment->thursday) $days[] = 'Thursday';
            if ($assignment->friday) $days[] = 'Friday';
            if ($assignment->saturday) $days[] = 'Saturday';
            if ($assignment->sunday) $days[] = 'Sunday';

            // If no days are selected, skip or use default
            if (empty($days)) {
                $this->warn("⚠️ Skipping assignment ID {$assignment->id}: No days selected.");
                Log::warning("Skipping assignment ID {$assignment->id}: No days selected.");
                continue;
            }

            $daysJson = json_encode($days, JSON_UNESCAPED_UNICODE);

            // Validate JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("❌ Invalid JSON for days in assignment ID {$assignment->id}: " . json_last_error_msg());
                Log::error("Invalid JSON for days in assignment ID {$assignment->id}: " . json_last_error_msg());
                continue;
            }

            // Insert or update task assignment in task_user_days
            try {
                DB::table('task_user_days')->updateOrInsert(
                    [
                        'task_id' => $newTaskId,
                        'user_id' => $newUserId,
                        'created_at' => $this->formatDate($assignment->created_at),
                    ],
                    [
                        'task_id' => $newTaskId,
                        'user_id' => $newUserId,
                        'days' => $daysJson,
                        'created_at' => $this->formatDate($assignment->created_at),
                        'updated_at' => $this->formatDate($assignment->updated_at),
                    ]
                );

                $this->line("✅ Imported assignment: Task ID {$newTaskId} for User ID {$newUserId}, Days: {$daysJson}");
                Log::info("Imported assignment: Task ID {$newTaskId}, User ID {$newUserId}, Days: {$daysJson}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to import assignment ID {$assignment->id}: {$e->getMessage()}");
                Log::error("Failed to import assignment ID {$assignment->id}: {$e->getMessage()}");
            }
        }

        $this->info('🎉 Done: Task and assignment import completed.');
        $taskCount = DB::table('tasks')->count();
        $taskUserDaysCount = DB::table('task_user_days')->count();
        $this->info("📊 Total records in tasks: {$taskCount}");
        $this->info("📊 Total records in task_user_days: {$taskUserDaysCount}");
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
        $users = DB::table('users')->select('id', 'email')->get();

        foreach ($users as $user) {
            $oldUserId = DB::connection('old_crm_db')
                ->table('acm_users')
                ->where('email', $user->email)
                ->where('is_deleted', 'no')
                ->value('id');

            if ($oldUserId) {
                $mapping[$oldUserId] = $user->id;
            }
        }

        return $mapping;
    }

    /**
     * Format date to handle invalid or null dates.
     *
     * @param string|null $value
     * @return string|null
     */
    protected function formatDate($value)
    {
        try {
            if (!$value || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
                return null;
            }
            return date('Y-m-d H:i:s', strtotime($value));
        } catch (\Exception $e) {
            Log::warning("Invalid date format: {$value}");
            return null;
        }
    }
}