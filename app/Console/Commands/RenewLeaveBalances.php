<?php

namespace App\Console\Commands;

use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RenewLeaveBalances extends Command {
    protected $signature = 'leaves:renew';
    protected $description = 'Renew leave balances quarterly';

    public function handle() {
        $users = User::all();
        foreach ($users as $user) {
            $today = Carbon::today();
            $joining = Carbon::parse($user->joining_date);
            $monthsSinceJoining = $joining->diffInMonths($today);

            $policies = LeavePolicy::all();
            foreach ($policies as $policy) {
                $balance = LeaveBalance::firstOrCreate([
                    'user_id' => $user->id,
                    'leave_policy_id' => $policy->id,
                ], ['balance' => 0, 'last_renewed_at' => $joining]);

                $probationEnd = $joining->copy()->addMonths($policy->probation_months);
                if ($today < $probationEnd) {
                    $balance->balance = 0; // No leaves during probation
                    $balance->save();
                    continue;
                }

                $lastRenewed = Carbon::parse($balance->last_renewed_at);
                if ($lastRenewed->diffInMonths($today) >= 3) {
                    // Renew: Reset to days_per_quarter (unused expire implicitly)
                    $balance->balance = $policy->days_per_quarter === 9999 ? 9999 : $policy->days_per_quarter;
                    $balance->last_renewed_at = $lastRenewed->addMonths(3); // Or today, but align to quarters
                    $balance->save();
                }
            }
        }
        $this->info('Leave balances renewed.');
    }
}