<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDepartments extends Command
{
    protected $signature = 'app:import-departments';
    protected $description = 'Import departments from old_crm_db.acm_departments to departments table with correct IDs';

    public function handle()
    {
        $this->info('🚀 Importing departments...');

        $departments = DB::connection('old_crm_db')
            ->table('acm_departments')
            ->where('isDeleted', 'no')
            ->get();

        foreach ($departments as $dept) {
            if (DB::table('departments')->where('id', $dept->id)->exists()) {
                $this->warn("⚠️ Skipping ID {$dept->id} ({$dept->department_name}) — already exists.");
                continue;
            }

            DB::table('departments')->insert([
                'id'          => $dept->id,
                'name'        => $dept->department_name,
                'description' => $dept->department_desc ?? $dept->department_name,
                'created_at'  => $this->fixDate($dept->created_at),
                'updated_at'  => $this->fixDate($dept->updated_at),
            ]);

            $this->info("✅ Inserted: [ID {$dept->id}] {$dept->department_name}");
        }

        $this->info('🎉 Done: Departments imported with correct IDs.');
    }

    protected function fixDate($value)
    {
        if (!$value || $value == '0000-00-00 00:00:00') {
            return now();
        }

        try {
            return date('Y-m-d H:i:s', strtotime($value));
        } catch (\Exception $e) {
            return now();
        }
    }
}
