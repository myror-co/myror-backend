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

class DeleteEnvironmentVariable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $website;
    protected $key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Website $website, $key)
    {
        $this->website = $website;
        $this->key = $key;
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

        //Delete variable
        $endpoint = 'https://api.vercel.com/v4/projects/'.$this->website->vercel_project_id.'/env/'.$this->key.'?target=production&teamId='.env('VERCEL_TEAM_ID');

        try{
            $response = $client->request('DELETE', $endpoint,[
                'headers' => [
                    'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
                ]
            ]);              
        } catch (Exception $e) {
            log::error($e);
        }
    }
}
