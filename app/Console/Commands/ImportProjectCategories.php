<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportProjectCategories extends Command
{
    protected $signature = 'app:import-project-categories';
    protected $description = 'Import categories from old CRM (acm_project_cats) to new CRM (project_categories)';

    public function handle()
    {
        $this->info('🔁 Starting category import...');

        $oldCategories = DB::connection('old_crm_db')
            ->table('acm_project_cats')
            ->orderBy('id') // Ensure parent categories are inserted first
            ->get();

        $inserted = 0;
        $skipped = 0;

        foreach ($oldCategories as $cat) {
            // Check if created_by_user_id exists in users table
            $createdBy = DB::table('users')->where('id', $cat->created_by_user_id)->exists()
                ? $cat->created_by_user_id
                : null;

            try {
                DB::table('project_categories')->insert([
                    'id'         => $cat->id, // Preserve old ID
                    'name'       => $cat->category_name,
                    'parent_id'  => $cat->parent_cat == 0 ? null : $cat->parent_cat,
                    'created_by' => $createdBy,
                    'created_at' => $this->parseDate($cat->created_at),
                    'updated_at' => $this->parseDate($cat->updated_at),
                ]);
                $inserted++;
            } catch (\Exception $e) {
                $this->error("❌ Failed to import category ID {$cat->id}: " . $e->getMessage());
                $skipped++;
            }
        }

        $this->info("✅ Import completed. Inserted: $inserted | Skipped: $skipped");
    }

    private function parseDate($date)
    {
        if (empty($date) || $date === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }
}
