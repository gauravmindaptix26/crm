<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SupportTicket;

class ImportTickets extends Command
{
    protected $signature = 'import:tickets';
    protected $description = 'Import tickets from old_crm_db.acm_tickets to laravelcrm.support_tickets';

    public function handle()
    {
        $this->info('Starting ticket import...');

        // Fetch tickets from old_crm_db.acm_tickets
        $oldTickets = DB::connection('old_crm_db')
            ->table('acm_tickets')
            ->where('isDeleted', 'no')
            ->whereNull('deleted_at')
            ->get();

        $total = $oldTickets->count();
        $this->info("Found {$total} tickets to import.");

        $successCount = 0;
        $errorCount = 0;

        // Cache old users from acm_users for faster lookup
        $oldUsers = DB::connection('old_crm_db')
            ->table('acm_users')
            ->select('id', 'email', 'name')
            ->get()
            ->keyBy('id');

        foreach ($oldTickets as $oldTicket) {
            try {
                // Map user_id
                $userId = null;
                if (isset($oldUsers[$oldTicket->created_by_user_id])) {
                    $oldUser = $oldUsers[$oldTicket->created_by_user_id];
                    $newUser = DB::table('users')
                        ->where('email', $oldUser->email)
                        ->orWhere('name', $oldUser->name)
                        ->first();
                    $userId = $newUser ? $newUser->id : 10949; // Default to Mandeep Singh
                    if (!$newUser) {
                        $this->warn("No matching user for old user_id {$oldTicket->created_by_user_id} (email: {$oldUser->email}, name: {$oldUser->name}), using default ID 10949");
                        \Log::warning("No matching user for old user_id {$oldTicket->created_by_user_id} (email: {$oldUser->email}, name: {$oldUser->name}), using default ID 10949");
                    }
                } else {
                    $this->warn("No user data for old user_id {$oldTicket->created_by_user_id}, using default ID 10949");
                    \Log::warning("No user data for old user_id {$oldTicket->created_by_user_id}, using default ID 10949");
                    $userId = 10949;
                }

                // Map assigned_to (nullable)
                $assignedTo = null;
                if ($oldTicket->assigned_to && isset($oldUsers[$oldTicket->assigned_to])) {
                    $oldAssignedUser = $oldUsers[$oldTicket->assigned_to];
                    $newAssignedUser = DB::table('users')
                        ->where('email', $oldAssignedUser->email)
                        ->orWhere('name', $oldAssignedUser->name)
                        ->first();
                    $assignedTo = $newAssignedUser ? $newAssignedUser->id : null;
                    if (!$newAssignedUser && $oldTicket->assigned_to) {
                        $this->warn("No matching user for old assigned_to {$oldTicket->assigned_to} (email: {$oldAssignedUser->email}, name: {$oldAssignedUser->name}), setting to NULL");
                        \Log::warning("No matching user for old assigned_to {$oldTicket->assigned_to} (email: {$oldAssignedUser->email}, name: {$oldAssignedUser->name}), setting to NULL");
                    }
                } elseif ($oldTicket->assigned_to) {
                    $this->warn("No user data for old assigned_to {$oldTicket->assigned_to}, setting to NULL");
                    \Log::warning("No user data for old assigned_to {$oldTicket->assigned_to}, setting to NULL");
                }

                // Validate user_id
                if (!DB::table('users')->where('id', $userId)->exists()) {
                    $this->warn("Invalid user_id {$userId} for ticket ID {$oldTicket->id}, using default ID 10949");
                    \Log::warning("Invalid user_id {$userId} for ticket ID {$oldTicket->id}, using default ID 10949");
                    $userId = 10949;
                }

                // Map priority
                $priority = $oldTicket->ticket_priority === 'normal' ? 'medium' : 'high';

                // Map status
                $status = $oldTicket->status;

                // Create new ticket
                SupportTicket::create([
                    'id' => $oldTicket->id,
                    'title' => $oldTicket->ticket_name,
                    'description' => $oldTicket->ticket_description,
                    'priority' => $priority,
                    'user_id' => $userId,
                    'assigned_to' => $assignedTo,
                    'status' => $status,
                    'created_at' => $oldTicket->created_at,
                    'updated_at' => $oldTicket->updated_at,
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to import ticket ID {$oldTicket->id}: {$e->getMessage()}");
                \Log::error("Failed to import ticket ID {$oldTicket->id}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        $this->info("Import completed. Successfully imported: {$successCount}, Failed: {$errorCount}");
    }
}