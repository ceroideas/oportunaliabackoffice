<?php

namespace Database\Seeders;

use App\Models\AuctionType;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AuctionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $auctionStatus = new AuctionType();
        $auctionStatus->name = 'En curso';
        $auctionStatus->save();

        $auctionStatus = new AuctionType();
        $auctionStatus->name = 'Finalizada';
        $auctionStatus->save();

        $auctionStatus = new AuctionType();
        $auctionStatus->name = 'Borrador';
        $auctionStatus->save();

        $auctionStatus = new AuctionType();
        $auctionStatus->name = 'Archivada';
        $auctionStatus->save();


    }

}
