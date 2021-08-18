<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $listing_name;
    public $first_name;
    public $last_name;
    public $guests;
    public $start;
    public $end;
    public $message;
    public $phone;
    public $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($listing_name, $first_name, $last_name, $guests, $start, $end, $message, $phone, $email)
    {
        $this->listing_name = $listing_name;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->guests = $guests;
        $this->start = $start;
        $this->end = $end;
        $this->message = $message;
        $this->phone = $phone;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('You have a new booking request 🛎️!')
                    ->markdown('emails.booking.request');
    }
}
