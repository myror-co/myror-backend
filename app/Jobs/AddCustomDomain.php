<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Website;

class AddCustomDomain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $website;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($website)
    {
        $this->website = $website;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.vercel.com/v8/projects/'.$this->website->vercel_project_id.'/domains?teamId='.env('VERCEL_TEAM_ID');

        $response = $client->request('POST', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
            ],
            'json' => [
                'name' => $this->website->custom_domain,
            ]
        ]);

        //Redirect to custom domain
        $endpoint = 'https://api.vercel.com/v8/projects/'.$this->website->vercel_project_id.'/domains/'.$this->website->name.env('DEFAULT_MYROR_DOMAIN').'?teamId='.env('VERCEL_TEAM_ID');

        $response = $client->request('PATCH', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
            ],
            'json' => [
                'redirect' => $this->website->custom_domain,
            ]
        ]);

        if ($response->getStatusCode() != 200)
        {
            throw new Exception("Error while adding the custom domain on vercel");
        }
    }
}
