<?php



namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\GuestPost;
use App\Models\User;

class ImportGuestPosts extends Command
{
    protected $signature = 'app:import-guest-posts';
    protected $description = 'Import guest posts from old CRM database';

    public function handle()
    {
        $this->info('🚀 Starting guest posts import...');

        $oldPosts = DB::connection('old_crm_db')
            ->table('acm_guest_posts')
            ->where('isDeleted', 'no')
            ->get();

        $this->info("Found {$oldPosts->count()} records to import.");

        $skipped = [];

        foreach ($oldPosts as $old) {
            $userExists = User::find($old->created_by_user_id);

            $newPost = GuestPost::updateOrCreate(
                ['website' => $old->website],
                [
                    'created_by'        => $userExists ? $old->created_by_user_id : null,
                    'website'           => $old->website,
                    'da'                => (int) $old->da,
                    'pa'                => (int) $old->pa,
                    'industry'          => $old->industry,
                    'country_id'        => $old->country_id,
                    'traffic'           => $old->traffic,
                    'publisher'         => $old->publisher_name,
                    'publisher_price'   => is_numeric($old->publisher_price) ? $old->publisher_price : 0,
                    'our_price'         => is_numeric($old->our_price) ? $old->our_price : 0,
                    'publisher_details' => $old->publisher_details,
                    'live_link'         => $old->live_link_publisher,
                    'created_at'        => $old->created_at,
                    'updated_at'        => $old->updated_at,
                ]
            );

            if (!$userExists) {
                $skipped[] = $old->created_by_user_id;
            }
        }

        if (!empty($skipped)) {
            $this->warn("⚠️ Skipped due to missing user IDs: " . implode(', ', array_unique($skipped)));
        }

        $this->info('✅ Guest posts import completed.');
    }
}

