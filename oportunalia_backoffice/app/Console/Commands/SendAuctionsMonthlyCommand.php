<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserAuction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAuctionsMonthlyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:auctions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'News monthly auctions';

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

        // TODO-DEBUG: implement this when Newsletters have been explained better

        // $auctions = Auction::where('start_date', '>=', $dateNow)
        //     ->orderBy('id', 'desc')
        //     ->take(100)
        //     ->get();

        // $users = User::where("deleted_at","=",null)->where("notification_news","=",1)->get();

        // foreach ($users as $user) {

        // }

        return 0;
    }
}
