<?php

namespace Database\Seeders;

use App\Models\AuctionType;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AuctionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $auctionType = new AuctionType();
        $auctionType->name = 'Auction';
        $auctionType->save();

        $auctionType = new AuctionType();
        $auctionType->name = 'Direct Sale';
        $auctionType->save();


    }

}
