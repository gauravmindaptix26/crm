<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubmissionSite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateMozMetrics extends Command
{
    // Command signature (how we run it)
    protected $signature = 'moz:update-metrics';

    // Command description
    protected $description = 'Fetch updated DA/PA/Spam Score for all sites via RapidAPI';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sites = SubmissionSite::all();

        foreach ($sites as $site) {
            try {
                $response = Http::withHeaders([
                    'X-RapidAPI-Key' => env('RAPIDAPI_KEY'),
                    'X-RapidAPI-Host' => env('RAPIDAPI_HOST'),
                    'Content-Type' => 'application/json',
                ])->post('https://domain-da-pa-checker.p.rapidapi.com/v1/getDaPa', [
                    'q' => $site->website_name
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $site->update([
                        'moz_da' => $data['domain_authority'] ?? null,
                        'moz_pa' => $data['page_authority'] ?? null,
                        'spam_score' => $data['spam_score'] ?? null,
                        // traffic is not provided by this API
                    ]);

                    Log::info("Updated metrics for {$site->website_name}");
                } else {
                    Log::error("Failed to fetch metrics for {$site->website_name}: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Error updating {$site->website_name}: " . $e->getMessage());
            }
        }

        $this->info('Moz metrics update completed!');
    }
}

