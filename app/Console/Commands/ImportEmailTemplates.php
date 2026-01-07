<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportEmailTemplates extends Command
{
    protected $signature = 'app:import-email-templates';
    protected $description = 'Import email templates from old CRM database to the new CRM database';

    public function handle()
    {
        $this->info('🔁 Starting email template import...');

        // Step 1: Get mapping of old user IDs to new user IDs (for added_by_user_id if needed, but not used in new table)
        $this->info('🔍 Fetching user ID mappings...');
        $oldToNewUserIdMap = $this->getUserIdMapping();
        if (empty($oldToNewUserIdMap)) {
            $this->warn('⚠️ No user ID mappings found. Proceeding without user mapping.');
            Log::warning('No user ID mappings found. Proceeding without user mapping.');
        } else {
            $this->info('✅ Found ' . count($oldToNewUserIdMap) . ' user ID mappings.');
            Log::info('User ID mappings: ' . json_encode($oldToNewUserIdMap));
        }

        // Step 2: Import email templates from acm_emails
        $this->info('📋 Importing email templates from acm_emails...');
        $oldEmails = DB::connection('old_crm_db')
            ->table('acm_emails')
            ->where('isDeleted', 'no')
            ->orderBy('id')
            ->get();

        if ($oldEmails->isEmpty()) {
            $this->warn('⚠️ No email templates found in acm_emails table.');
            Log::warning('No email templates found in acm_emails table.');
            return 0;
        } else {
            $this->info('🔍 Found ' . $oldEmails->count() . ' email templates to process.');
        }

        foreach ($oldEmails as $oldEmail) {
            // Format dates
            $createdAt = $this->formatDate($oldEmail->created_at);
            $updatedAt = $this->formatDate($oldEmail->updated_at);

            // Log date values
            Log::info("Email Template ID {$oldEmail->id}: Old created_at={$oldEmail->created_at}, Formatted={$createdAt}, Old updated_at={$oldEmail->updated_at}, Formatted={$updatedAt}");

            // Insert or update email template in the new email_templates table
            try {
                DB::table('email_templates')->updateOrInsert(
                    [
                        'title' => $oldEmail->title,
                    ],
                    [
                        'title' => $oldEmail->title,
                        'subject' => $oldEmail->subject,
                        'from_email' => $oldEmail->fromemail ?? '',
                        'body' => $oldEmail->body,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]
                );

                $this->line("✅ Imported email template: {$oldEmail->title} (Old ID: {$oldEmail->id})");
            } catch (\Exception $e) {
                $this->error("❌ Failed to import email template ID {$oldEmail->id}: {$e->getMessage()}");
                Log::error("Failed to import email template ID {$oldEmail->id}: {$e->getMessage()}");
            }
        }

        $this->info('🎉 Done: All email templates imported successfully.');
        $emailTemplateCount = DB::table('email_templates')->count();
        $this->info("📊 Total records in email_templates: {$emailTemplateCount}");
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
            return null;
        }
    }
}