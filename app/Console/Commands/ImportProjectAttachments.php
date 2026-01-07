<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImportProjectAttachments extends Command
{
    protected $signature = 'app:import-project-attachments';
    protected $description = 'Import project images from old CRM database to the new CRM database';

    public function handle()
    {
        $this->info('🔁 Starting project attachments import at ' . Carbon::now()->format('Y-m-d H:i:s') . ' IST...');

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

        // Step 2: Check schema of project_attachments table
        $this->info('🔍 Checking project_attachments table schema...');
        try {
            $columns = DB::select('DESCRIBE project_attachments');
            Log::info('Project attachments table schema: ' . json_encode($columns));
            $this->info('✅ Project attachments table schema: ' . json_encode(array_column($columns, 'Field')));
        } catch (\Exception $e) {
            $this->error('❌ Failed to fetch project_attachments table schema: ' . $e->getMessage());
            Log::error('Failed to fetch project_attachments table schema: ' . $e->getMessage());
            return 1;
        }

        // Step 3: Get a default project ID
        $defaultProjectId = null;
        try {
            $firstProject = DB::table('projects')->select('id')->first();
            if ($firstProject !== null) {
                $defaultProjectId = $firstProject->id;
            }
            if ($defaultProjectId === null) {
                $this->error('⚠️ No projects found in the projects table. Please create a default project.');
                Log::error('No projects found in the projects table.');
                return 1;
            }
            Log::info('Default project ID: ' . $defaultProjectId);
        } catch (\Exception $e) {
            $this->error('❌ Failed to fetch default project ID: ' . $e->getMessage());
            Log::error('Failed to fetch default project ID: ' . $e->getMessage());
            return 1;
        }

        // Step 4: Import project images from acm_project_images
        $this->info('📋 Importing project images from acm_project_images...');
        try {
            $oldProjectImagesQuery = DB::connection('old_crm_db')
                ->table('acm_project_images')
                ->where('is_Deleted', 'no')
                ->orderBy('id');

            // Log all project images and relevant values
            $allProjectImagesCount = DB::connection('old_crm_db')->table('acm_project_images')->count();
            $isDeletedValues = DB::connection('old_crm_db')
                ->table('acm_project_images')
                ->select('is_Deleted')
                ->distinct()
                ->pluck('is_Deleted')
                ->toArray();
            $projectIds = DB::connection('old_crm_db')
                ->table('acm_project_images')
                ->select('project_id')
                ->distinct()
                ->pluck('project_id')
                ->toArray();
            Log::info('Total project images in acm_project_images (all): ' . $allProjectImagesCount);
            Log::info('Distinct is_Deleted values in acm_project_images: ' . json_encode($isDeletedValues));
            Log::info('Distinct project_id values in acm_project_images: ' . json_encode($projectIds));

            $oldProjectImages = $oldProjectImagesQuery->get();
            $totalProjectImages = $oldProjectImagesQuery->count();
            if ($oldProjectImages->isEmpty()) {
                $this->warn('⚠️ No project images found in acm_project_images table.');
                Log::warning('No project images found in acm_project_images table.');
            } else {
                $this->info('🔍 Found ' . $totalProjectImages . ' project images to process.');
                Log::info('Project images found in acm_project_images: ' . $totalProjectImages);
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to fetch project images from acm_project_images: ' . $e->getMessage());
            Log::error('Failed to fetch project images from acm_project_images: ' . $e->getMessage());
            return 1;
        }

        $importedCount = 0;
        foreach ($oldProjectImages as $oldImage) {
            // Validate project_id
            $newProjectId = $oldImage->project_id > 0 && DB::table('projects')->where('id', $oldImage->project_id)->exists() ? $oldImage->project_id : $defaultProjectId;
            if ($oldImage->project_id <= 0 || !DB::table('projects')->where('id', $oldImage->project_id)->exists()) {
                $this->warn("⚠️ Project Image ID {$oldImage->id}: Invalid project_id {$oldImage->project_id}. Using default project ID {$defaultProjectId}.");
                Log::warning("Project Image ID {$oldImage->id}: Invalid project_id {$oldImage->project_id}. Using default project ID {$defaultProjectId}.");
            }

            // Format dates
            $createdAt = $this->formatDate($oldImage->created_at, 'project_attachments', $oldImage->id, false);
            $updatedAt = $this->formatDate($oldImage->updated_at, 'project_attachments', $oldImage->id, true);

            // Log date values
            Log::info("Project Image ID {$oldImage->id}: Old created_at={$oldImage->created_at}, Formatted={$createdAt}, Old updated_at={$oldImage->updated_at}, Formatted={$updatedAt}");

            // Handle original_name (truncate to 255 chars for varchar field)
            $originalName = strlen($oldImage->image_name) > 255 ? substr($oldImage->image_name, 0, 255) : $oldImage->image_name;

            // Extract extension from image_name or attachment_type
            $extension = pathinfo($oldImage->image_name, PATHINFO_EXTENSION);
            if (!$extension) {
                // Fallback to attachment_type
                $extensionMap = [
                    'pdf' => 'pdf',
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'text/plain' => 'txt',
                    // Add more mappings as needed
                ];
                $extension = $extensionMap[$oldImage->attachment_type] ?? 'bin';
                $this->warn("⚠️ Project Image ID {$oldImage->id}: No extension in image_name {$oldImage->image_name}. Using extension '{$extension}' from attachment_type.");
                Log::warning("Project Image ID {$oldImage->id}: No extension in image_name {$oldImage->image_name}. Using extension '{$extension}' from attachment_type.");
            }

            // Generate unique identifier for file_path
            $uniqueId = Str::random(32); // Generates a 32-character random string
            $filePath = "project_attachments/{$uniqueId}.{$extension}";

            // Handle mime_type
            $mimeType = $oldImage->attachment_type ?: 'application/octet-stream';
            if (!preg_match('/^[a-zA-Z0-9\/\-\+]+$/', $mimeType)) {
                $this->warn("⚠️ Project Image ID {$oldImage->id}: Invalid mime_type {$mimeType}. Using default 'application/octet-stream'.");
                Log::warning("Project Image ID {$oldImage->id}: Invalid mime_type {$mimeType}. Using default 'application/octet-stream'.");
                $mimeType = 'application/octet-stream';
            }

            // Prepare attachment data
            $attachmentData = [
                'id' => $oldImage->id,
                'project_id' => $newProjectId,
                'file_path' => $filePath,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            // Insert or update attachment using raw SQL
            try {
                DB::transaction(function () use ($attachmentData) {
                    // Log attachment data before insert
                    Log::info("Inserting project attachment ID {$attachmentData['id']}: " . json_encode($attachmentData));
                    DB::statement(
                        'INSERT INTO project_attachments (id, project_id, file_path, original_name, mime_type, created_at, updated_at) ' .
                        'VALUES (?, ?, ?, ?, ?, ?, ?) ' .
                        'ON DUPLICATE KEY UPDATE ' .
                        'project_id = VALUES(project_id), file_path = VALUES(file_path), original_name = VALUES(original_name), ' .
                        'mime_type = VALUES(mime_type), created_at = VALUES(created_at), updated_at = VALUES(updated_at)',
                        [
                            $attachmentData['id'],
                            $attachmentData['project_id'],
                            $attachmentData['file_path'],
                            $attachmentData['original_name'],
                            $attachmentData['mime_type'],
                            $attachmentData['created_at'],
                            $attachmentData['updated_at'],
                        ]
                    );
                });

                $importedCount++;
                $this->line("✅ Imported project attachment: ID {$oldImage->id}, Project ID: {$newProjectId}, File Path: {$filePath}, Original Name: {$originalName}, Created: {$createdAt}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to import project attachment ID {$oldImage->id}: {$e->getMessage()}");
                Log::error("Failed to import project attachment ID {$oldImage->id}: {$e->getMessage()}");
            }
        }

        $this->info('✅ Imported ' . $importedCount . ' project attachments.');
        Log::info('Imported project attachments count: ' . $importedCount);

        // Verify total records
        $attachmentCount = DB::table('project_attachments')->count();
        $this->info("📊 Total records in project_attachments: {$attachmentCount}");

        // Verify no null created_at values
        $nullCreatedAtCount = DB::table('project_attachments')->whereNull('created_at')->count();
        if ($nullCreatedAtCount > 0) {
            $this->error("❌ Found {$nullCreatedAtCount} project attachments with null created_at. Fixing now...");
            Log::error("Found {$nullCreatedAtCount} project attachments with null created_at.");
            $nullAttachmentIds = DB::table('project_attachments')->whereNull('created_at')->pluck('id')->toArray();
            Log::error("Project attachments with null created_at: " . json_encode($nullAttachmentIds));
            DB::table('project_attachments')
                ->whereNull('created_at')
                ->update(['created_at' => Carbon::now()->format('Y-m-d H:i:s')]);
            $this->info("✅ Fixed {$nullCreatedAtCount} project attachments by setting created_at to current timestamp.");
        } else {
            $this->info("✅ No project attachments with null created_at found.");
        }

        return 0;
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