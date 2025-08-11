<?php

namespace App\Console\Commands;

use App\Mail\EndAdministrator;
use App\Models\Auction;
use App\Models\AuctionStatus;
use App\Models\AuctionType;
use App\Models\Bid;
use App\Models\User;
use App\Models\Role;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Http;

class CheckCesion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cesion:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check ended cesions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        date_default_timezone_set('Europe/Madrid');
        
        $dateNow = new \DateTime();
        echo $dateNow->format('Y-m-d H:i:s');

        $auctions = Auction::whereIn("auction_type_id", [AuctionType::CESION,4])
            ->where("auction_status_id", AuctionStatus::ONGOING)
            ->where("end_date", "<=", $dateNow)
            ->get();

        foreach ($auctions as $auction)
        {
            $auction->auction_status_id = AuctionStatus::UNSOLD;
            $auction->save();

            $this->deleteFromFotocasa($auction);

            $auction->parseForEmail();

            Notification::create([
                'title' => __('notifications.direct_sale_end.title'),
                'subtitle' => '',
                'auction_id' => $auction->id,
                'type_id' => Notification::CESION_END,
            ]);

            $this->sendEndAdministrator($auction);

        }

        return 0;
    }

    private function sendEndAdministrator($auction)
    {
        $users = User::whereNull('deleted_at')
            ->where('confirmed', 1)
            ->where('role_id', Role::ID_ADMIN)
            ->get();

        foreach ($users as $user)
        {
            Mail::to($user->email)
                ->send(new EndAdministrator($user, $auction));
        }
    }

    public function deleteFromFotocasa($auction)
    {
        $response = Http::withHeaders([
            'Api-Key' => "G921CBlEVogm16vF5DTWhQt8qtPg65Pac50ud7sdZRVPKqT1FNF8NLg9KOehnhKE",
            'X-Source' => "2af813dd-057a-4995-911a-0b4004ecbdd7",
            'Content-Type' => 'application/json'
        ])->delete('https://imports.gw.fotocasa.pro/api/v2/property/'.base64_encode($auction['id']));

        $response->throw();

        \Log::info('Eliminado de fotocasa '.$auction['id']);

        return $response;
    }
}
