<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Contact extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;
    private $data;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $token
     * @param $data
     */
    public function __construct($data)
    {
        $this->subject(config('app.name'));
        $this->from(env('MAIL_FROM_ADDRESS'), config('app.name'));
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.contact', [
            'firstname' =>$this->data['firstname'],
            'lastname'=>$this->data['lastname'],
            'email'=>$this->data['email'],
            'phone'=>$this->data['phone'],
            'subject'=>$this->data['subject'],
            'message'=>$this->data['message'],
        ]);
    }
}
