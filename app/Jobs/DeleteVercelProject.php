<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteVercelProject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $website_name;

    public $tries = 1;
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($website_name)
    {
        $this->website_name = $website_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.vercel.com/v1/projects/'.$this->website_name.'?teamId='.env('VERCEL_TEAM_ID');
        $response = $client->request('DELETE', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN'),
            ]
        ]);

        if ($response->getStatusCode() != 204)
        {
            throw new Exception("Error while trying to delete project from Vercel");
        }
    }
}
