<?php

namespace App\Console\Commands;

use App\Mail\AuctionEnd;
use App\Mail\WinBid;
use App\Mail\FavEnd;
use App\Models\Auction;
use App\Models\AuctionStatus;
use App\Models\AuctionType;
use App\Models\Bid;
use App\Models\Favorite;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserAuction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Http;

class CheckAuctions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auction:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check ended auctions';

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
        // Starting auctions

        date_default_timezone_set('Europe/Madrid'); // agregado para corresponder la hora con españa

        $dateNow = new \DateTime();
        echo $dateNow->format('Y-m-d H:i:s');

        $auctions = Auction::where("auction_status_id", AuctionStatus::SOON)
            //->where("auction_type_id", AuctionType::AUCTION)
            ->where("start_date", "<=", $dateNow)
            ->where("end_date", ">", $dateNow)
            ->get();

        foreach ($auctions as $auction)
        {
            $auction->auction_status_id = AuctionStatus::ONGOING;
            $auction->background = 1;
            $auction->save();
        }

        // Finished auctions

        $dateNow = new \DateTime();
        $dateNow->setTime($dateNow->format('H'), $dateNow->format('i'), 0);

        $auctions = Auction::where("auction_status_id", AuctionStatus::ONGOING)
            ->where("auction_type_id", AuctionType::AUCTION)
            ->where("end_date", "<=", $dateNow)
            ->get();

        foreach ($auctions as $auction)
        {   if($auction->auction_type_id == AuctionType::DIRECT_SALE || $auction->auction_type_id == AuctionType::CESION || $auction->auction_type_id == 4){continue;}
            $bid = Bid::select("bids.*", "users.*")
                ->where("auction_id", $auction->id)
                ->join("users", "bids.user_id", "=", "users.id")
                ->orderBy("import", "DESC")
                ->first();

            if ($bid)
            {
                $auction->auction_status_id = AuctionStatus::FINISHED;
                $auction->save();

                $this->deleteFromFotocasa($auction);

                Notification::create([
                    'title' => __('notifications.auction_end_win.title', [
                        'firstname' => $bid->firstname,
                        'lastname' => $bid->lastname,
                    ]),
                    'subtitle' => __('notifications.auction_end_win.subtitle', [
                        'import' => $bid->import,
                    ]),
                    'user_id' => $bid->user_id,
                    'auction_id' => $bid->auction_id,
                    'type_id' => Notification::AUCTION_END_WIN,
                ]);

                $bid->parseForEmail();

                $this->sendEmailWin($auction, $bid);
            }
            else
            {
                $auction->auction_status_id = AuctionStatus::ARCHIVED;
                $auction->save();

                $this->deleteFromFotocasa($auction);

                Notification::create([
                    'title' => __('notifications.auction_end.title'),
                    'subtitle' => '',
                    'auction_id' => $auction->id,
                    'type_id' => Notification::AUCTION_END,
                ]);
            }

            $usersDeposits = \App\Models\User::select('users.*')
                ->join('deposits', 'deposits.user_id', '=', 'users.id')
                ->where('deposits.auction_id', $auction->id)
                ->where('deposits.status', 1);

            if ($bid)
            {
                $usersDeposits->where('users.id', '!=', $bid->user_id);
            }

            $usersDeposits = $usersDeposits->get()
                ->unique('id');

            $usersFavs = \App\Models\User::select('users.*')
                ->join('favorites', 'favorites.user_id', '=', 'users.id')
                ->where('favorites.auction_id', $auction->id);

            if ($bid)
            {
                $usersFavs->where('users.id', '!=', $bid->user_id);
            }

            $usersFavs = $usersFavs->get();

            $this->sendEmailFavEnd($auction, $usersFavs);
            $this->sendAuctionEnd($auction, $usersDeposits->diff($usersFavs));
        }

        return 0;
    }

    private function sendEmailWin($auction, $bid)
    {
        $auction->parseForEmail();
        $user = User::find($bid->user_id);
        if ($user->notification_auctions) {
            Mail::to($user->email)
                ->send(new WinBid($user, $bid, $auction));
        }
    }

    private function sendEmailFavEnd($auction, $users)
    {
        $auction->parseForEmail();

        foreach ($users as $user)
        {
            if ($user->notification_favorites) {
                Mail::to($user->email)
                    ->send(new FavEnd($user, $auction));
            }
        }
    }

    private function sendAuctionEnd($auction, $users)
    {
        $auction->parseForEmail();

        foreach ($users as $user)
        {
            if ($user->notification_auctions) {
                Mail::to($user->email)
                    ->send(new AuctionEnd($user, $auction));
            }
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

        return $response;
    }
}
