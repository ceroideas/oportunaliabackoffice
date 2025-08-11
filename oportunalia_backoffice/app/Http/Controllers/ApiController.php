<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

use App\Models\User;
use App\Models\Favorite;
use App\Models\DirectSaleOffer;
use App\Models\Auction;
use App\Models\AuctionType;
use App\Models\Bid;
use App\Models\Notification;
use App\Models\AuctionStatus;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use DB;
use Illuminate\Http\Request;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApiController extends Controller
{
    protected $code = 404;
    protected $response = [];
    protected $messages = [];
    protected $total = 0;

    public function sendResponse()
    {
        return response()->json([
            'response' => $this->response,
            'messages' => $this->messages,
            'total' => $this->total,
            'code' => $this->code,
            'time' => date('c')
        ], $this->code);
    }

    public function test(Request $r)
    {
        if ($r->test == 1) {

            /*Schema::table('notifications', function(Blueprint $table) {
                //
                $table->integer('notification_status')->nullable();
            });*/

            // return Notification::get()->last();

            // return Active::where('name','Activo de prueba Ceroideas')->first();

            /*$at = new AuctionType;
            $at->name = "Cesión de Crédito";
            $at->save();*/

            return AuctionType::all();
            /*$auctions = Auction::where('auction_type_id',2)->where('auction_status_id',1)->get();

            foreach ($auctions as $key => $value) {
                $value->background = 1;
                $value->save();
            }*/

            /*$response = Http::withHeaders([
                'Api-Key' => "G921CBlEVogm16vF5DTWhQt8qtPg65Pac50ud7sdZRVPKqT1FNF8NLg9KOehnhKE",
                'X-Source' => "2af813dd-057a-4995-911a-0b4004ecbdd7",
                'Content-Type' => 'application/json'
            ])->delete('https://imports.gw.fotocasa.pro/api/v2/property/'.base64_encode(6692));

            $response->throw();*/

            // return $auctions;

        }else{

            $data = $this->getPlaceDetails("ChIJ1U_waTRGOKERcVtat3bJMmQ");

            foreach ($data['result']['reviews'] as $key => &$value) {
                $value['image_url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/800px-Google_%22G%22_logo.svg.png";
            }
            return $data['result'];
        }
    }

    function getPlaceDetails($placeId) {
        $apiKey = "AIzaSyALrXOtjf-VGndljqeKZsA07bJJ8F0XwQw";
        $url = "https://maps.googleapis.com/maps/api/place/details/json?language=es&place_id={$placeId}&fields=name,rating,reviews&key={$apiKey}";

        // Inicializar cURL
        $ch = curl_init();

        // Configurar opciones
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Ejecutar solicitud
        $response = curl_exec($ch);

        // Manejo de errores
        if (curl_errno($ch)) {
            return ['error' => curl_error($ch)];
        }

        curl_close($ch);

        // Retornar datos en formato JSON
        return json_decode($response, true);
    }
  
}
