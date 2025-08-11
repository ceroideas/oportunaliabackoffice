<?php

namespace App\Console\Commands;

use App\Mail\FavToEnd;
use App\Mail\FavStart;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Favorite;
use App\Models\UserAuction;
use Illuminate\Console\Command;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Mail;

class CheckFavs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'favs:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Favs';

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
        
        $dateNowStart = new \DateTime();
        $dateNowStart->setTime($dateNowStart->format('H'), $dateNowStart->format('i'), 0);
        $dateNowStart2 = new \DateTime();
        $dateNowStart2->setTime($dateNowStart2->format('H'), $dateNowStart2->format('i'), 59);

        echo 'dateNowStart:' . $dateNowStart->format('Y-m-d H:i:s') . "\n";
        echo 'dateNowStart2:' . $dateNowStart2->format('Y-m-d H:i:s') . "\n";

        $dateNowEnd = new \DateTime();
        $dateNowEnd->setTime($dateNowEnd->format('H'), $dateNowEnd->format('i') + 60, 0);
        $dateNowEnd2 = new \DateTime();
        $dateNowEnd2->setTime($dateNowEnd2->format('H'), $dateNowEnd2->format('i') + 60, 59);

        echo 'dateNowEnd:' . $dateNowEnd->format('Y-m-d H:i:s') . "\n";
        echo 'dateNowEnd2:' . $dateNowEnd2->format('Y-m-d H:i:s') . "\n";

        $auctionsStart = Auction::where("start_date", ">=", $dateNowStart)
            ->where("start_date", "<=", $dateNowStart2)
            ->get();

        $auctionsEnd = Auction::where("end_date", ">=", $dateNowEnd)
            ->where("end_date", "<=", $dateNowEnd2)
            ->get();

        foreach ($auctionsStart as $auction)
        {
            $favs = Favorite::where("auction_id", $auction->id)->where("status", 0)->get();

            foreach ($favs as $fav)
            {
                $user = User::find($fav->user_id);

                if ($user->notification_favorites)
                {
                    $auction->parseForEmail();
                    $this->favStart($user, $auction);
                }

                $fav->status = 1;
                $fav->save();
            }
        }

        foreach ($auctionsEnd as $auction)
        {
            $favs = Favorite::where("auction_id", $auction->id)->where("status", 1)->get();

            foreach ($favs as $fav)
            {
                $user = User::find($fav->user_id);

                if ($user->notification_favorites)
                {
                    $auction->parseForEmail();
                    $this->favToEnd($user, $auction);
                }

                $fav->status = 2;
                $fav->save();
            }
        }

        return 0;
    }

    private function favStart($user, $auction)
    {
        if ($user->notification_favorites) {
            Mail::to($user->email)
                ->send(new FavStart($user, $auction));
        }
    }

    private function favToEnd($user, $auction)
    {
        if ($user->notification_favorites) {
            Mail::to($user->email)
                ->send(new FavToEnd($user, $auction));
        }
    }
}
