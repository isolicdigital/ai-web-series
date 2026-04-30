<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddToAWeberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $email;
    protected $name;
    public $delay = 3600; // 1 hour in seconds

    public function __construct($email, $name)
    {
        $this->email = $email;
        $this->name = $name;
    }

    public function handle()
    {
        try {
            $apiUrl = config('services.aweber.endpoint', 'https://softprohub.com/api/aweber/subscribe');
            $tag = config('app.name');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'email' => $this->email,
                'name' => $this->name,
                'source' => config('app.name'),
                'app_id' => config('app.name'),
                'tags' => [$tag],
            ]);

            Log::info('AWeber (delayed) - Before Success', ['email' => $this->email]);

            if ($response->successful()) {
                Log::info('User added to AWeber (delayed)', ['email' => $this->email]);
                return true;
            }

            Log::warning('Failed to add user to AWeber', [
                'email' => $this->email, 
                'status' => $response->status()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('AWeber API call failed', [
                'email' => $this->email, 
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}