<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class LogController extends Controller
{
    public function viewLog()
    {
        $logFilePath = storage_path('logs/laravel.log');
        
        // Check if file exists
        if (!File::exists($logFilePath)) {
            return Response::make('Log file not found', 404);
        }

        // Read the log file content
        $logContent = File::get($logFilePath);

        // Return content with proper response
        return response($logContent, 200)->header('Content-Type', 'text/plain');
    }
}

