<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FavStart extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $auction;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $auction)
    {
        //$this->subject(config('app.name').' - '.__('emails.favs.start.subject'));
        $this->from(env('MAIL_FROM_ADDRESS'), config('app.name'));
        $this->user = $user;
        $this->auction = $auction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $this->auction->auction_type_id == 1 ? $venta = 'subasta' : ($this->auction->auction_type_id == 2 ? $venta = 'venta directa' : $venta = 'cesión de remate');

        $this->subject(config('app.name').' - La '.ucfirst($venta).__('emails.favs.start.subject'));

        return $this->markdown('emails.favs.start', [
            'firstname' => $this->user->firstname,
            'date' => date('d/m/Y', strtotime($this->auction->end_date)),
            'time' => date('H:i', strtotime($this->auction->end_date)),
            'path' => $this->auction->path,
            'product' => $this->auction,
            'title' => $this->auction->title,
            'venta'=>$venta,
        ]);
    }
}
