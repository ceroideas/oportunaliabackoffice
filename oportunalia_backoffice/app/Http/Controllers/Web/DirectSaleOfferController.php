<?php



namespace App\Http\Controllers\Web;



use App\Http\Controllers\ApiController;

use App\Mail\OfferReceived;

use App\Models\Auction;

use App\Models\DirectSaleOffer;

use App\Models\User;

use App\Models\Notification;

use App\Rules\Dni;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;

use Symfony\Component\HttpFoundation\Response as ResponseAlias;

use Illuminate\Support\Facades\Http;



class DirectSaleOfferController extends ApiController

{

    public function create(Request $request,$guid)

    {

        $auction = Auction::where('link_rewrite', $guid)->first();



        $dateNow = new \DateTime();

        $dateStartAuction = new \DateTime($auction->start_date);

        $dateEndAuction = new \DateTime($auction->end_date);



        if ($dateNow < $dateStartAuction || $dateNow > $dateEndAuction)

        {

            $this->messages[] = 'Cannot make an offer on a direct sale that have been finished or have not started yet';

            $this->code = 418;

            return $this->sendResponse();

        }





        $rules = [

            //'import' => 'required|min:'.$auction->minimum_bid ?? 0,

            'import' => 'required|min:0',

        ];



        $validator = Validator::make($request->all(), $rules);



        if ($validator->fails()){

            $this->messages[] = $validator->errors()->messages();

            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;



        }else{



            if ($request->import< $auction->minimum_bid) {

                $this->messages[] = 'La oferta es inferior al mínimo establecido';

                $this->code = 419;

            }else{



                $lastUserOffer = DirectSaleOffer::where('auction_id', $auction->id)

                ->where('user_id', Auth::id())

                ->orderBy('id', 'desc')

                ->first();

                if(Auth::user()->status == 0)
                {
                    $this->messages[] = 'El usuario debe estar validado para poder ofertar';

                    $this->code = 419;



                    return $this->sendResponse();
                }



                if($lastUserOffer && $lastUserOffer->import >= $request->import ){

                        $this->messages[] = 'Esta oferta es menor a su anterior oferta';

                        $this->code = 419;



                        return $this->sendResponse();

                }else{

                    DirectSaleOffer::create(array_merge(

                        $validator->validated(),

                        [

                            "user_id" => Auth::id(),

                            "auction_id" => $auction->id

                        ]

                    ));



                    $direct_offer = DirectSaleOffer::with(["auction"])->where("auction_id",$auction->id)->where("user_id",Auth::id())->orderBy('id', 'desc')->first();

                    $user = User::find(Auth::id());

                    Mail::to($user->email)->send(new OfferReceived($user, $direct_offer , "".$direct_offer->auction->title , "".$direct_offer->auction->guid , "".$direct_offer->import , "".$direct_offer->created_at ));

                    $this->newNotification( auth()->user() , "".$direct_offer->import , $auction );

                    $this->code = ResponseAlias::HTTP_CREATED;



                    /* PipeDrive Change */

                    if(isset($user->wp_id)){



                        if(file_exists('testing/pipedrive.txt')){

                            $file = fopen('testing/pipedrive.log', 'a');

                            fwrite($file, date("d/m/Y H:i:s").'-'.'Persona con wp_id: '.$user->wp_id.PHP_EOL);

                            fclose($file);

                        }



                        /* Check label (Person Status) */

                        $responsePerson = Http::withHeaders([

                            'Content-Type' => 'application/json',

                            'Accept' => 'application/json'

                        ])->get(config('pipedrive.pipeurl').'persons/'.$user->wp_id.'?api_token='.config('pipedrive.pitk'))->throw()->json();

                        // Label = 6 = Hot Lead



                        if(file_exists('testing/pipedrive.txt')){

                            $file = fopen('testing/pipedrive.log', 'a');

                            fwrite($file, date("d/m/Y H:i:s").'-'.'ResponsePerson: '.$responsePerson['data']['label'].PHP_EOL);

                            fclose($file);

                        }

                        // Person con label 6 (hotlead, no ha realizado ofertas) y 5 (customer, ya tiene una oferta)

                        // si label == 6 entonces debe crear una oferta nueva y update a person para que sea label = 5 customer

                        // si label == 5

                        if($responsePerson['data']['label']==5){



                                /* Debemos comprobar si el deal es el mismo ya que un usuario con label customer(5) puede hacer mas de una oferta sobre un deal*/

                                /* Podemos buscar por deal */

                                $responseDealTmp = Http::withHeaders([

                                    'Content-Type' => 'application/json',

                                    'Accept' => 'application/json'

                                ])->get(config('pipedrive.pipeurl').'deals/search?term='.$auction->auto.'&person_id='.$user->wp_id.'&api_token='.config('pipedrive.pitk'))->throw()->json();





                                if(!empty($responseDealTmp['data']['items'])){

                                    $dealId = $responseDealTmp['data']['items'][0]['item']['id'];

                                    $personId = $responseDealTmp['data']['items'][0]['item']['person']['id'];

                                    // hacemos el update del deal en cuestion

                                    $responseDealAdd = Http::withHeaders([

                                        'Content-Type' => 'application/json',

                                        'Accept' => 'application/json'

                                    ])->put(config('pipedrive.pipeurl').'deals/'.$dealId.'?api_token='.config('pipedrive.pitk'),[

                                        'title'=> 'Oferta incrementada '.$user->firstname .' '. $user->lastname,

                                        'value'=> $request->import,

                                        "823953a6d449ef677c433808d5acce033346199b"=>$request->import

                                        ])->throw()->json();



                                        if(file_exists('testing/pipedrive.txt')){

                                            $file = fopen('testing/pipedrive.log', 'a');

                                            fwrite($file, date("d/m/Y H:i:s").'-'.'Persona label customer con oferta previa en este activo'.PHP_EOL);

                                            fwrite($file, date("d/m/Y H:i:s").'-'.'Success web: '. $responseDealAdd['success'] .PHP_EOL);

                                            fwrite($file, date("d/m/Y H:i:s").'-'.'Response web: '. $responseDealAdd['data']['id'].PHP_EOL);

                                            fclose($file);

                                        }



                                }else{

                                    // Usuario customer pero sin oferta previa sobre este lead

                                    /* PIPEDRIVE: Creamos lead (Deal) */

                                    $responseDeal = Http::withHeaders([

                                        'Content-Type' => 'application/json',

                                        'Accept' => 'application/json'

                                    ])->post(config('pipedrive.pipeurl').'deals?api_token='.config('pipedrive.pitk'), [

                                        'title'=> 'Oferta realizada '.$user->firstname .' '. $user->lastname,

                                        'value'=> $request->import,

                                        'currency'=>'EUR',

                                        'user_id'=>config('pipedrive.owner_id'),

                                        'person_id'=>$responsePerson['data']['id'],

                                        'org_id'=>'',

                                        'pipeline_id'=>'',

                                        'stage_id'=>config('pipedrive.ofertado'),

                                        'status'=>'open',

                                        'add_time'=>now(),

                                        'probability'=>'',

                                        'visible_to'=>'3',

                                        /* No acepta la config, tenemos que meter los campos con el id entre comillas dobles */

                                        /*config('pipedrive.origen_negocio')=>19,  // Origen de negocio Oportunalia

                                        config('pipedrive.tipo_venta')=> $auction->auction_type_id == 2 ? 24 : 22,  // Tipo de venta, 24 venta, 22 cesion

                                        /*config('pipedrive.referencia_activo')=>$auction->auto, // Referencia del activo

                                        config('pipedrive.tipo_activo')=>$auction->active->active_category_id, // Tipo de activo,

                                        config('pipedrive.provincia_id')=>$auction->active->province_id, // Provincia del activo

                                        config('pipedrive.precio_activo')=>$request->import, // Precio del activo*/

                                        "5727248a7a285cfe39b54747b167295fe3157c96"=>"19",

                                        "c41361b07cdedeb9dbc0c0d0c5eea9aabbd082bb"=>$auction->auction_type_id == 2 ? 24 : 22,

                                        "57f6df842a30b6c6babddd9a80326a3394cde26f" => $auction->auto,

                                        "2779b3346acbcb0b0469641b9b4a49a14511e5e6" =>$auction->activo->active_category->pipe_id, // tipo activo

                                        "ef814a1aaddde881295bd841f9be93fba8e395f3"=>$auction->activo->province->pipe_id,

                                        "823953a6d449ef677c433808d5acce033346199b"=>$request->import

                                    ])->throw()->json();



                                    /* No hace falta actualizar al usuario

                                    $responsePerson = Http::withHeaders([

                                        'Content-Type' => 'application/json',

                                        'Accept' => 'application/json'

                                    ])->put(config('pipedrive.pipeurl').'persons/'.$responsePerson['data']['id'].'?api_token='.config('pipedrive.pitk'),[

                                        'label'=> config('pipedrive.customer'),

                                        ])->throw()->json();*/



                                    if(file_exists('testing/pipedrive.txt')){

                                        $file = fopen('testing/pipedrive.log', 'a');

                                        fwrite($file, date("d/m/Y H:i:s").'-'.'Persona label customer pero sin oferta previa sobre este activo'.PHP_EOL);

                                        fwrite($file, date("d/m/Y H:i:s").'-'.'Success web: '. $responseDeal['success'] .PHP_EOL);

                                        fwrite($file, date("d/m/Y H:i:s").'-'.'Response web: '. $responseDeal['data']['id'].PHP_EOL);

                                        fclose($file);

                                    }



                                }



                        }elseif($responsePerson['data']['label']==6 || $responsePerson['data']['label']==7){

                            // find deal, we need the id

                            $response = Http::withHeaders([

                                'Content-Type' => 'application/json',

                                'Accept' => 'application/json'

                            ])->get(config('pipedrive.pipeurl').'deals/search?term=Usuario&person_id='.$user->wp_id.'&api_token='.config('pipedrive.pitk'))->throw()->json();





                            if(!empty($response['data']['items'])){

                                $dealId = $response['data']['items'][0]['item']['id'];

                                $personId = $response['data']['items'][0]['item']['person']['id'];



                                //update deal and person



                                $responseDeal = Http::withHeaders([

                                    'Content-Type' => 'application/json',

                                    'Accept' => 'application/json'

                                ])->put(config('pipedrive.pipeurl').'deals/'.$dealId.'?api_token='.config('pipedrive.pitk'),[

                                    'title'=> 'Oferta realizada '.$user->firstname .' '. $user->lastname,

                                    'value'=> $request->import,

                                    'currency'=>'EUR',

                                    'user_id'=>config('pipedrive.owner_id'),

                                    'person_id'=>$responsePerson['data']['id'],

                                    'org_id'=>'',

                                    'pipeline_id'=>'',

                                    'stage_id'=>config('pipedrive.ofertado'),

                                    'status'=>'open',

                                    'add_time'=>now(),

                                    'probability'=>'',

                                    'visible_to'=>'3',

                                    "5727248a7a285cfe39b54747b167295fe3157c96"=>"19",

                                    "c41361b07cdedeb9dbc0c0d0c5eea9aabbd082bb"=>$auction->auction_type_id == 2 ? 24 : 22,

                                    "57f6df842a30b6c6babddd9a80326a3394cde26f" => $auction->auto,

                                    "2779b3346acbcb0b0469641b9b4a49a14511e5e6" =>$auction->activo->active_category->pipe_id, // tipo activo

                                    "ef814a1aaddde881295bd841f9be93fba8e395f3"=>$auction->activo->province->pipe_id,

                                    "823953a6d449ef677c433808d5acce033346199b"=>$request->import



                                    ])->throw()->json();



                                $responsePerson = Http::withHeaders([

                                    'Content-Type' => 'application/json',

                                    'Accept' => 'application/json'

                                ])->put(config('pipedrive.pipeurl').'persons/'.$personId.'?api_token='.config('pipedrive.pitk'),[

                                    'label'=> config('pipedrive.customer'),

                                    ])->throw()->json();



                                if(file_exists('testing/pipedrive.txt')){

                                    $file = fopen('testing/pipedrive.log', 'a');

                                    fwrite($file, date("d/m/Y H:i:s").'-'.'Persona no es customer y tiene un lead cualificado, hacemos un nuevo deal'.PHP_EOL);

                                    fwrite($file, date("d/m/Y H:i:s").'-'.'Update Deal: '. $dealId . PHP_EOL);

                                    fwrite($file, date("d/m/Y H:i:s").'-'.'Update person: '. $personId .PHP_EOL);

                                    fclose($file);

                                }

                            }



                            if(file_exists('testing/pipedrive.txt')){

                                $file = fopen('testing/pipedrive.log', 'a');

                                fwrite($file, date("d/m/Y H:i:s").'-'.'PipeUpdate actualizado' . PHP_EOL);

                                fclose($file);

                            }

                        }

                    }

                }

            }

        }



        return $this->sendResponse();

    }





    private function newNotification($user, $amount, $auction)

    {

        Notification::create([

            'title' => __('notifications.offer.title', [

                'firstname' => $user->firstname,

                'lastname' => $user->lastname,

            ]),

            'subtitle' => __('notifications.offer.subtitle', [

                'amount' => $amount,

            ]),

            'user_id' => $user->id,

            'auction_id' => $auction->id,

            'type_id' => Notification::OFFER,

        ]);

    }





}

