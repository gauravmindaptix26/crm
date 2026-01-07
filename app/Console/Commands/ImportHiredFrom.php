<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OldAcmHiredFrom;
use App\Models\HiredFrom;
use Carbon\Carbon;

class ImportHiredFrom extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-hired-from';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = OldAcmHiredFrom::where('isDeleted', 'no')->get();
        $this->info("Found {$records->count()} records to import.");

        foreach ($records as $record) {
            HiredFrom::create([
                'name' => $record->name,
                'description' => $record->desc,
                'created_at' => $record->created_at ?? Carbon::now(),
                'updated_at' => $record->updated_at ?? Carbon::now(),
            ]);
        }

        $this->info("Import complete.");
    }
}
