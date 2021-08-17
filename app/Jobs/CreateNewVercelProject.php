<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Website;

class CreateNewVercelProject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $website;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Website $website)
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
        //Create new project
        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.vercel.com/v6/projects?teamId='.env('VERCEL_TEAM_ID');

        $response = $client->request('POST', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
            ],
            'json' => ['name' => $this->website->name]
        ]);

        if ($response->getStatusCode() != 200)
        {
            throw new Exception("Error while creating the project on vercel");
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $this->website->vercel_alias_domain = $data['alias'][0]['domain'];
        $this->website->vercel_project_id = $data['id'];
        $this->website->save();

        //Add *.myror.website domain
        $endpoint = 'https://api.vercel.com/v1/projects/'.$this->website->vercel_project_id.'/alias?teamId='.env('VERCEL_TEAM_ID');

        $response = $client->request('POST', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
            ],
            'json' => [
                'domain' => $this->website->name.env('DEFAULT_MYROR_DOMAIN'),
            ]
        ]);

        //Create env variable - NEVER EMPTY !
        $endpoint = 'https://api.vercel.com/v6/projects/'.$this->website->vercel_project_id.'/env?teamId='.env('VERCEL_TEAM_ID');

        $response = $client->request('POST', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
            ],
            'json' => [
                'type' => 'plain',
                'key' => 'NEXT_PUBLIC_WEBSITE_API_ID',
                'value' => $this->website->api_id,
                'target' => ['production']
            ]
        ]);

        $response = $client->request('POST', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
            ],
            'json' => [
                'type' => 'plain',
                'key' => 'NEXT_PUBLIC_API_BASE_URL',
                'value' => env('API_URL').'/api',
                'target' => ['production']
            ]
        ]);

        $response = $client->request('POST', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
            ],
            'json' => [
                'type' => 'plain',
                'key' => 'NEXT_PUBLIC_STRIPE_API_KEY',
                'value' => env('STRIPE_KEY'),
                'target' => ['production']
            ]
        ]);
    }
}
