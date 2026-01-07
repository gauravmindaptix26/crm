<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('project_categories')->insert([
            'name' => 'Animals & Pets',
            'parent_id' => null, // This makes it a root category
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
