<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Website;

class DeleteCustomDomain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $website;
    protected $domain_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Website $website, $domain_name)
    {
        $this->website = $website;
        $this->domain_name = $domain_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new \GuzzleHttp\Client();

        //Delete redirection custom domain
        $endpoint = 'https://api.vercel.com/v8/projects/'.$this->website->vercel_project_id.'/domains='.$this->website->name.env('DEFAULT_MYROR_DOMAIN').'?teamId='.env('VERCEL_TEAM_ID');

        $response = $client->request('PATCH', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
            ],
            'json' => [
                'redirect' => '',
            ]
        ]);

        //Delete custom domain
        $endpoint = 'https://api.vercel.com/v8/projects/'.$this->website->vercel_project_id.'/domains='.$this->domain_name.'?teamId='.env('VERCEL_TEAM_ID');

        $response = $client->request('DELETE', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
            ]
        ]);

        if ($response->getStatusCode() != 200)
        {
            throw new Exception("Error while deleting the custom domain on vercel");
        }
    }
}
