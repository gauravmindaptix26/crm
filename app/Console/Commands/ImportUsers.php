<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;


class ImportUsers extends Command
{
    protected $signature = 'app:import-users';
    protected $description = 'Import users from old CRM database and map reporting persons correctly';

    public function handle()
    {
        $this->info('🔁 Starting user import...');

        // Map department names to new department IDs
        $departmentMap = DB::table('departments')->pluck('id', 'name');

        // Get old users
        $oldUsers = DB::connection('old_crm_db')
            ->table('acm_users')
            ->where('is_deleted', 'no')
            ->orderBy('id')
            ->get();

        // Old ID to new ID map
        $oldToNewUserIdMap = [];

        // Step 1: Insert/update users
        foreach ($oldUsers as $old) {
            $departmentName = DB::connection('old_crm_db')
                ->table('acm_departments')
                ->where('id', $old->department_id)
                ->where('isDeleted', 'no')
                ->value('department_name');

            $validDepartmentId = $departmentMap[$departmentName] ?? null;
            $joiningDate = $this->formatDate($old->date_of_joining);

            $user = User::updateOrCreate(
                ['email' => $old->email],
                [
                    'name'               => $old->name,
                    'email'              => $old->email,
                    'phone_number'       => $old->phone_no,
                    'image'              => $old->profile_img,
                    'password' => Hash::make('seocrm@123'),
                    'experience'         => $old->experience,
                    'date_of_joining'    => $joiningDate,
                    'qualification'      => $old->qualification,
                    'specialization'     => $old->specialization,
                    'department_id'      => $validDepartmentId,
                    'monthly_target'     => $old->monthly_target,
                    'monthly_salary'     => $old->monthly_salary,
                    'upsell_incentive'   => $old->upsell_incentive,
                    'employee_code'      => $old->employee_code,
                    'allow_all_projects' => $old->view_all_projects ?? 0,
                    'disable_login'      => $old->login_disabled_by_admin ?? 0,
                    'user_role'          => $old->special_admin ? 'Admin' : 'User',
                    'created_at'         => $this->formatDate($old->created_at),
                    'updated_at'         => $this->formatDate($old->updated_at),
                ]
            );

            // Assign role
            $roleRecord = DB::connection('old_crm_db')
                ->table('acm_user_roles')
                ->where('user_id', $old->id)
                ->first();

            $mappedRoleName = $this->mapOldRoleIdToRoleName($roleRecord->role_id ?? null);
            $finalRole = $mappedRoleName ?: 'Employee';

            $role = Role::firstOrCreate(['name' => $finalRole]);
            $user->syncRoles([$role]);

            // Save mapping
            $oldToNewUserIdMap[$old->id] = $user->id;

            $this->line("✅ Imported: {$user->email} (Old ID: {$old->id} → New ID: {$user->id}) | Role: {$finalRole}");
        }

        // Step 2: Assign reporting_person AFTER all users are created
        $this->info('🔄 Updating reporting_person relationships...');

        foreach ($oldUsers as $old) {
            if (!empty($old->reporting_person)) {
                $newUserId = $oldToNewUserIdMap[$old->id] ?? null;
                $newReportingId = $oldToNewUserIdMap[$old->reporting_person] ?? null;

                if ($newUserId && $newReportingId) {
                    User::where('id', $newUserId)->update([
                        'reporting_person' => $newReportingId
                    ]);
                    $this->line("👤 {$old->email} now reports to ID: {$newReportingId}");
                } else {
                    $this->warn("⚠️ Failed to assign reporting_person for: {$old->email}");
                }
            }
        }

        $this->info('🎉 Done: All users and reporting_person links created successfully.');
    }

    protected function mapOldRoleIdToRoleName($roleId)
    {
        return match ((int) $roleId) {
            1 => 'Admin',
            2 => 'Project Manager',
            3 => 'Team Lead',
            4 => 'Employee',
            5 => 'Sales Team',
            6 => 'HR',
            7 => 'Sales Team Manager',
            default => null,
        };
    }

    protected function formatDate($value)
    {
        try {
            if (!$value || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
                return null;
            }
            return date('Y-m-d', strtotime($value));
        } catch (\Exception $e) {
            return null;
        }
    }
}
