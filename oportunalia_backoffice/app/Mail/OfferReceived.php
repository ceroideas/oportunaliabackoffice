<?php

namespace App\Mail;

use App\Models\Assets;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferReceived extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $offer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $offer,$title,$guid,$import,$created_at)
    {
        $this->subject(config('app.name').' - '.__('emails.offers.received.subject'));
        $this->from(env('MAIL_FROM_ADDRESS'), config('app.name'));
        $this->user = $user;
        $this->offer = $offer;
        $this->title = $title;
        $this->guid = $guid;
        $this->import = $import;
        $this->created_at = $created_at;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.offers.received', [
            'firstname' => $this->user->firstname,
            'title' => $this->title,
            'guid' => $this->guid,
            'import' => $this->import,
            'created_at' => $this->created_at,
        ]);
    }
}
