<?php

namespace App\Jobs;

use Exception;
use Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\WebsiteVercelBuilt;
use Illuminate\Support\Facades\Mail;

class UpdateBuildStatusVercel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info($this->payload);

        $website = \App\Models\Website::where('vercel_project_id', $this->payload['data']['payload']['projectId'])->first();

        if ($website) 
        {
            $user = $website->user;

            switch ($this->payload['data']['type']) 
            {
                case 'deployment':
                    $website->status = 'deploying';
                    $website->save();
                break;
                
                case 'deployment-ready':
                    $website->status = 'built';
                    $website->save();
        
                    $site_url = $website->custom_domain ? 'https://'.$website->custom_domain : 'https://'.$website->name.'myror.website';

                    //Send mail
                    Mail::to($user->email)->queue(new WebsiteVercelBuilt($user->first_name, $website->name, $site_url));
                break;
            } 
        }
    }
}
