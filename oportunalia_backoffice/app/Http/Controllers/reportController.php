<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

use App\Models\Province;
use App\Models\Auction;
use App\Models\Deposit;
use App\Models\Active;
use App\Models\User;
use App\Models\Bid;
use App\Models\Campaign;
use App\Models\Participant;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Carbon\Carbon;

class reportController extends ApiController
{
    public function report(Request $request){

        $auctions = Auction::all();
        //$auctions = Auction::where('auction_type_id',1)->get(); // Misma estructura
        $this->response = $auctions;
        $this->total = $auctions->count();
        $this->code = ResponseAlias::HTTP_OK;


        $filename = [
            config('app.name'),
            __('fields.backoffice.auctions'),
        ];

        //$filename = composeFilename($filename, 'xlsx');
        $filename = 'all_auctions.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Admin\AllExport, $filename
        );

        return $this->sendResponse();

    }

    public function subastas(Request $request){

        $auctions = Auction::where('auction_type_id',"=",1)->get();
        //$auctions = Auction::where('auction_type_id',1)->get(); // Misma estructura
        $this->response = $auctions;
        $this->total = $auctions->count();
        $this->code = ResponseAlias::HTTP_OK;


        $filename = [
            config('app.name'),
            __('fields.backoffice.auctions'),
        ];

        //$filename = composeFilename($filename, 'xlsx');
        $filename = 'subastas.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Admin\AuctionExport, $filename
        );

        return $this->sendResponse();

    }

    public function ventas(Request $request){

        $auctions = Auction::where('auction_type_id',"=",2)->get();
        //$auctions = Auction::where('auction_type_id',1)->get(); // Misma estructura
        $this->response = $auctions;
        $this->total = $auctions->count();
        $this->code = ResponseAlias::HTTP_OK;


        $filename = [
            config('app.name'),
            __('fields.backoffice.auctions'),
        ];

        //$filename = composeFilename($filename, 'xlsx');
        $filename = 'ventas.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Admin\DirectSaleExport, $filename
        );

        return $this->sendResponse();

    }

   public function depositos(Request $request){

        $deposits = Deposit::all();
        //$auctions = Auction::where('auction_type_id',1)->get(); // Misma estructura
        $this->response = $deposits;
        $this->total = $deposits->count();
        $this->code = ResponseAlias::HTTP_OK;


        $filename = [
            config('app.name'),
            __('fields.backoffice.deposits'),
        ];

        //$filename = composeFilename($filename, 'xlsx');
        $filename = 'depositos.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Admin\DepositExport, $filename
        );

        return $this->sendResponse();

    }

    public function activos(Request $request){

        $actives = Active::all();
        $this->response = $actives;
        $this->total = $actives->count();
        $this->code = ResponseAlias::HTTP_OK;

        $filename = [
            config('app.name'),
            __('fields.backoffice.actives'),
        ];

        //$filename = composeFilename($filename, 'xlsx');
        $filename = 'activos.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Admin\ActiveExport, $filename
        );

        return $this->sendResponse();

    }

    public function usuarios(Request $request){

        $users = User::all();
        $this->response = $users;
        $this->total = $users->count();
        $this->code = ResponseAlias::HTTP_OK;

        $filename = [
            config('app.name'),
            __('fields.backoffice.users'),
        ];

        $filename = 'usuarios.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Admin\UserExport, $filename
        );

        return $this->sendResponse();

    }

    public function pujas(Request $request){

        $bids = Bid::all();
        $this->response = $bids;
        $this->total = $bids->count();
        $this->code = ResponseAlias::HTTP_OK;

        $filename = [
            config('app.name'),
            __('fields.backoffice.bids'),
        ];

        $filename = 'pujas.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Admin\BidExport, $filename
        );

        return $this->sendResponse();
    }

    public function participants(Request $request){

        $participants = Participant::all();
        //$auctions = Auction::where('auction_type_id',1)->get(); // Misma estructura
        $this->response = $participants;
        $this->total = $participants->count();
        $this->code = ResponseAlias::HTTP_OK;


        $filename = [
            config('app.name'),
            __('fields.backoffice.participants'),
        ];

        //$filename = composeFilename($filename, 'xlsx');
        $filename = 'all_participants'.'-'.Carbon::now()->format('d-m-Y_h_i').'.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Admin\ParticipantExport, $filename
        );

        return $this->sendResponse();

    }

}
