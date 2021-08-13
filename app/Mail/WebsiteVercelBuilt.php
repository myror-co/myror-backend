<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WebsiteVercelBuilt extends Mailable
{
    use Queueable, SerializesModels;

    public $user_first_name;
    public $website_name;
    public $website_url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($first_name, $website_name, $website_url)
    {
        $this->first_name = $first_name;
        $this->website_name = $website_name;
        $this->website_url = $website_url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('no-reply@myror.co')
                    ->subject('Your website '.$this->website_name.' is online!')
                    ->markdown('emails.vercel.built');
    }
}
