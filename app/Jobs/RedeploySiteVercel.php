<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Website;

class RedeploySiteVercel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $website;

    public $tries = 1;
    public $timeout = 300;

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
        //Update website status
        $this->website->status = 'sending_files';
        $this->website->save();

        $template_name = 'carlton';

        $sha1_array=array();
        $files = Storage::disk('template_'.$template_name)->allFiles();

        //Upload files on Vercel
        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.vercel.com/v2/files?teamId='.env('VERCEL_TEAM_ID');

        foreach ($files as $key => $file) {

            $sha1_array[] = array(
                'file' => $file,
                'sha' => sha1_file(storage_path('templates/'.$template_name.'/').$file),
                'size' => Storage::disk('template_'.$template_name)->size($file),
            );

            $response = $client->request('POST', $endpoint,[
                'headers' => [
                    'Authorization' => 'Bearer '.env('VERCEL_TOKEN'),
                    'Content-Length' => Storage::disk('template_'.$template_name)->size($file),
                    'x-now-digest' => sha1_file(storage_path('templates/'.$template_name.'/').$file),
                ],
                'body' => Storage::disk('template_'.$template_name)->get($file)
            ]);

            if ($response->getStatusCode() != 200)
            {
                throw new Exception("Error while posting the files to vercel");
            }
        }

        $endpoint = 'https://api.vercel.com/v13/deployments?teamId='.env('VERCEL_TEAM_ID');
        $response = $client->request('POST', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN'),
            ],
            'json' => [
                'name' => $this->website->name,
                'files' => $sha1_array,
                'target' => 'production',
                "projectSettings" => [
                    'framework' => 'nextjs',
                    'devCommand' => null,
                    'buildCommand' => null,
                    'outputDirectory' => null,
                    'rootDirectory' => null,
                ]
            ]
        ]);

        if ($response->getStatusCode() != 200)
        {
            throw new Exception("Error while trigerring a deployment on vercel");
        }
    }
}
