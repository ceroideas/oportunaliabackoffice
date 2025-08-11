<?php

namespace App\Mail;

use App\Models\Assets;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RepresentationInValid extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $representation;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $representation
     */
    public function __construct($user, $representation)
    {
        $this->subject(config('app.name').' - '.__('emails.representation.invalid.subject'));
        $this->from(env('MAIL_FROM_ADDRESS'), config('app.name'));
        $this->user = $user;
        $this->representation = $representation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.representation.invalid', [
            'firstname' => $this->user->firstname,
            'representation' => $this->representation->firstname . ' ' . $this->representation->lastname,
            'idNumber' => $this->representation->document_number,
        ]);
    }
}
