<?php

namespace App\Jobs;

use Log;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Website;

class AddNewEnvironmentVariable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $website;
    protected $key;
    protected $value;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Website $website, $key, $value)
    {
        $this->website = $website;
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //Initiate client
        $client = new \GuzzleHttp\Client();

        //Create env variable
        $endpoint = 'https://api.vercel.com/v8/projects/'.$this->website->vercel_project_id.'/env?teamId='.env('VERCEL_TEAM_ID');

        $response = $client->request('POST', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
            ],
            'json' => [
                'type' => 'plain',
                'key' => $this->key,
                'value' => $this->value,
                'target' => ['production']
            ]
        ]);
    }
}
