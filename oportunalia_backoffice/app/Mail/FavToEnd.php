<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FavToEnd extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $auction;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user,  $auction)
    {
        //$this->subject(config('app.name').' - '.__('emails.favs.to_end.subject'));
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

        $this->subject(config('app.name').' - '.ucfirst($venta).__('emails.favs.to_end.subject'));

        return $this->markdown('emails.favs.to_end', [
            'firstname' => $this->user->firstname,
            'date' => date('d/m/Y', strtotime($this->auction->end_date)),
            'time' => date('H:i', strtotime($this->auction->end_date)),
            'lastMinutes' => 2,
            'bidTimeInterval' => $this->auction->bid_time_interval,
            'path' => $this->auction->path,
            'product' => $this->auction,
            'title' => $this->auction->title,
            'venta'=>$venta,
        ]);
    }
}
