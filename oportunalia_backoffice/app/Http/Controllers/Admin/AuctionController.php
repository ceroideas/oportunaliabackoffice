<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Services\FotocasaMapper;
use App\Http\Resources\ActiveImagesResource;
use App\Http\Resources\AuctionResource;
use App\Http\Resources\AuctionResourceWeb;
use App\Http\Resources\DepositResource;
use App\Models\Active;
use App\Models\ActiveImages;
use App\Models\Archive;
use App\Models\Auction;
use App\Models\AuctionStatus;
use App\Models\AuctionType;
use App\Models\Bid;
use App\Models\Deposit;
use App\Models\DirectSaleOffer;
use App\Models\Favorite;
use App\Models\Migration\Representation;
use App\Models\Representation as UserRepresentation;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use function route;
use PDF;
use DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use App\Imports\ActivesImport;
use App\Imports\AuctionsImport;


class AuctionController extends ApiController
{
    public function __construct()
    {
        $this->description = nl2br("Somos OPORTUNALIA, entidad especializada en la realización de activos procedente de ejecuciones hipotecaria y procedimientos concursales.
         
        INFORMACIÓN DE INTERÉS:
        •              Inmueble procedente de ejecución hipotecaria en fase de cesión de remate
        •              Operación no hipotecable
        •              Visitas no disponibles
        •              Situación posesoria desconocida
        •              Deudas pendientes asociadas al inmueble desconocidas y asumidas por el adquirente (ejemplo IBI, tasas de basura, comunidad de propietarios…)
        •              Impuestos transmisión asumidos por el adquirente
        •              Nuestro equipo legal podrá tramitar el procedimiento judicial hasta la obtención de las llaves (consulta nuestros precios)
         
        ¿Quieres saber más? Regístrate de forma gratuita en nuestra página web para estar al día de las oportunidades de inversión que vamos publicando y poder presentar tu oferta. En la web podrás acceder a la información del activo (certificación catastral, dirección…)
         
        Contacta con nosotros y te explicaremos el procedimiento.
         
        Te acompañaremos hasta la toma de posesión.
         
        Para más información accede a nuestra página web www.oportunalia.com o mándanos un email a nuestro correo info@oportunalia.com o llámanos a nuestro teléfono 91 125 45 30.");
    }
    /**
     * Stores data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function create(Request $request)
    {
        date_default_timezone_set('Europe/Madrid');
        
        $dateNow = (new \DateTime())->format('Y-m-d H:i:s');

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'active_id' => 'required',
            'auction_status_id' => 'required',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s|after:'.$dateNow,
            'appraisal_value' => 'required|numeric',
            'start_price' => 'required|numeric',
            'deposit' => 'nullable|numeric',
            'minimum_bid' => 'nullable|numeric',
            'commission' => 'required|numeric',
            'bid_price_interval' => 'required|numeric',
            'bid_time_interval' => 'required|integer',
            'video' => 'nullable|string',
            'video_file' => 'nullable|string',
            'idealista' => 'nullable|string',
            'rrss' => 'nullable|string',
            'repercusion' => 'nullable|string',
            'mailing' => 'nullable|string',
            'auto' => 'nullable|string',
            'juzgado' => 'nullable|string',
            'description' => '',
            'land_registry' => '',
            'technical_specifications' => '',
            'conditions' => '',
            'featured' => '',
            'asignado' => '',
            'dontshowtimer' => '',
            'link_rewrite' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ])
        ->after(function ($validator) use ($request) {

            if ($request->has('link_rewrite') && $request->get('link_rewrite') != null)
            {
                $auctions = Auction::where('link_rewrite', $request->get('link_rewrite'))
                    ->count();

                if ($auctions > 0) {
                    $validator->errors()->add('link_rewrite', 'validation:used');
                }
            }
        })
        ->after(function ($validator) use ($request) {

            if ($request->has('active_id') && $request->get('active_id') != null)
            {
                $auctions = Auction::where('active_id', $request->get('active_id'))
                    ->count();

                if ($auctions > 0) {
                    $validator->errors()->add('active_id', 'validation:used');
                }
            }
        });

        /*$validator2 = Validator::make($request->all(), [
            'description_document' => 'nullable|mimes:pdf',
            'technical_document' => 'nullable|mimes:pdf',
            'land_registry_document' => 'nullable|mimes:pdf',
            'conditions_document' => 'nullable|mimes:pdf',
            'description_document_two' => 'nullable|mimes:pdf',
            'technical_document_two' => 'nullable|mimes:pdf',
            'land_registry_document_two' => 'nullable|mimes:pdf',
            'conditions_document_two' => 'nullable|mimes:pdf',
        ]);*/

        if ($validator->fails() /*|| $validator2->fails()*/)
        {
            $this->messages[] = array_merge(
                $validator->errors()->messages(),
                //$validator2->errors()->messages()
            );
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $values = $validator->validated();

            switch ($values['auction_status_id'])
            {
                case AuctionStatus::ONGOING:

                    $dateNow = new \DateTime();
                    $dateNow->setTime($dateNow->format('H'), $dateNow->format('i') - 5, 0);

                    $dateStart = new \DateTime($values['start_date']);

                    if ($dateStart > $dateNow) {
                        $values['auction_status_id'] = AuctionStatus::SOON;
                    }
                    break;
            }

            $technical_archive_id;
            $description_archive_id;
            $land_registry_archive_id;
            $conditions_archive_id;

            /*Optional Documents*/
            if($request->file("description_document") != null && $request->file("description_document") != ""){
                $fileDescription = Storage::disk("public")->put("", $request->file("description_document"));
                $description_archive = Archive::create(["name" => $request->file("description_document")->getClientOriginalName(), "path" => $fileDescription]);
                $description_archive_id = $description_archive->id;
            }else{ $technical_archive_id = NULL; }

            if($request->file("technical_document") != null && $request->file("technical_document") != ""){
                $fileTechnical = Storage::disk("public")->put("", $request->file("technical_document"));
                $technical_archive = Archive::create(["name" => $request->file("technical_document")->getClientOriginalName(), "path" => $fileTechnical]);
                $technical_archive_id = $technical_archive->id;
            }else{ $description_archive_id = NULL; }

            if($request->file("land_registry_document") != null && $request->file("land_registry_document") != ""){
                $fileLandRegistry = Storage::disk("public")->put("", $request->file("land_registry_document"));
                $land_registry_archive = Archive::create(["name" => $request->file("land_registry_document")->getClientOriginalName(), "path" => $fileLandRegistry]);
                $land_registry_archive_id = $land_registry_archive->id;
            }else{ $land_registry_archive_id = NULL; }

            if($request->file("conditions_document") != null && $request->file("conditions_document") != ""){
                $fileConditions = Storage::disk("public")->put("", $request->file("conditions_document"));
                $conditions_archive = Archive::create(["name" => $request->file("conditions_document")->getClientOriginalName(), "path" => $fileConditions]);
                $conditions_archive_id = $conditions_archive->id;
            }else{ $conditions_archive_id = NULL; }

            /*Optional Documents*/



            /*OPTIONAL (TWO) DOCUMENTS*/
            $technical_archive_two_id = NULL;
            $description_archive_two_id = NULL;
            $land_registry_archive_two_id = NULL;
            $conditions_archive_two_id = NULL;

            if($request->file("description_document_two") != null && $request->file("description_document_two") != ""){
                $fileDescription_two = Storage::disk("public")->put("", $request->file("description_document_two"));
                $description_archive_two = Archive::create(["name" => $request->file("description_document_two")->getClientOriginalName(), "path" => $fileDescription_two]);
                $description_archive_id_two = $description_archive_two->id;
            }else{ $technical_archive_id_two = NULL; }

            if($request->file("technical_document_two") != null && $request->file("technical_document_two") != ""){
                $fileTechnical_two = Storage::disk("public")->put("", $request->file("technical_document_two"));
                $technical_archive_two = Archive::create(["name" => $request->file("technical_document_two")->getClientOriginalName(), "path" => $fileTechnical_two]);
                $technical_archive_id_two = $technical_archive_two->id;
            }else{ $description_archive_two_id = NULL; }

            if($request->file("land_registry_document_two") != null && $request->file("land_registry_document_two") != ""){
                $fileLandRegistry_two = Storage::disk("public")->put("", $request->file("land_registry_document_two"));
                $land_registry_archive_two = Archive::create(["name" => $request->file("land_registry_document_two")->getClientOriginalName(), "path" => $fileLandRegistry_two]);
                $land_registry_archive_two_id = $land_registry_archive_two->id;
            }else{ $land_registry_archive_two_id = NULL; }

            if($request->file("conditions_document_two") != null && $request->file("conditions_document_two") != ""){
                $fileConditions_two = Storage::disk("public")->put("", $request->file("conditions_document_two"));
                $conditions_archive_two = Archive::create(["name" => $request->file("conditions_document_two")->getClientOriginalName(), "path" => $fileConditions_two]);
                $conditions_archive_two_id = $conditions_archive_two->id;
            }else{ $conditions_archive_two_id = NULL; }
            /*OPTIONAL (TWO) DOCUMENTS*/


            $auction = Auction::create(array_merge($values,
                [
                    'auction_status_id' => $values['auction_status_id'],
                    'guid' => (string) Str::uuid(),
                    'auction_type_id' => AuctionType::AUCTION,
                    'technical_archive_id' => $technical_archive_id,
                    'description_archive_id' => $description_archive_id,
                    'land_registry_archive_id' => $land_registry_archive_id,
                    'conditions_archive_id' => $conditions_archive_id,
                    'technical_archive_two_id' => $technical_archive_id_two,
                    'description_archive_two_id' => $description_archive_two_id,
                    'land_registry_archive_two_id' => $land_registry_archive_two_id,
                    'conditions_archive_two_id' => $conditions_archive_two_id
                ]
            ));
            

            $this->code = ResponseAlias::HTTP_CREATED;

            if ($values['auction_status_id'] == AuctionStatus::ONGOING) {
                $this->syncWithFotocasa($auction);
            }
           
        }



        return $this->sendResponse();
    }

    /**
     * Returns a list of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listAll(Request $request)
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (!Schema::hasColumn('auctions', 'background')) {
                $table->string('background')->nullable();
            }
        });

        $auctionQuery = Auction::selectraw(
            "auctions.id,auctions.title,
            Count(bids.id) as bids,
            Max(bids.import) as max_bid,
            auctions.start_date,
            auctions.end_date,
            auctions.deposit,
            auctions.start_price,
            auctions.auction_status_id,
            auctions.featured,
            auctions.asignado,
            auctions.dontshowtimer,
            auctions.link_rewrite,
            auction_statuses.name as status,
            auctions.auto",
        )
        ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
        ->join("actives", "auctions.active_id", "=", "actives.id")
        ->leftJoin("bids", "auctions.id", "=", "bids.auction_id")
        ->where("auction_type_id", 1)
        ->groupBy("auctions.id");

        $auctionQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('id', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('auto', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('title', 'LIKE', '%' . $request->input('search') . '%');
        });

        $auctionQuery->when($request->has('auction_status_id'), function (Builder $builder) use ($request) {
            $builder->where('auctions.auction_status_id', '=', $request->input('auction_status_id'));
        });

        $auctionQuery->when($request->has('start_date'), function (Builder $builder) use ($request) {
            $builder->where('auctions.start_date', '>=', $request->input('start_date'));
        });

        $auctionQuery->when($request->has('end_date'), function (Builder $builder) use ($request) {
            $builder->where('auctions.end_date', '<=', $request->input('end_date'));
        });

        $auctionQuery->when($request->has('selected'), function (Builder $builder) use ($request) {
            $builder->whereNotIn('auctions.id', explode(',', $request->input('selected')) );
        });

        $auctionQuery->when($request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $auctions = $auctionQuery->get();

        $this->response = $auctions;
        $this->total = $auctions->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    // public function syncWithFotocasa ($auction) {
    //     try {
    //         $typeMapping = FotocasaMapper::mapCategory($auction->auction_type_id);

    //         $response = Http::get(url("api/auction/{$auction->link_rewrite}"));

            
            
    //         if (!$response->successful()) {
    //             throw new \Exception("Error al obtener auction: ".$response->status());
    //         }
    
    //         $auctionData = $response->json()['response'];

    //         $fotocasaData = [
    //             "ExternalId" => (string) $auctionData->id,  
    //             "AgencyReference" => $auctionData->auto,
    //             'TypeId' => (int) $typeMapping['type'],  
    //             "ContactTypeId" => 3,
    //             'SubTypeId' => isset($typeMapping['subtype']) ? (int) $typeMapping['subtype'] : null,
    //             "PropertyAddress" => [
    //                 [
    //                     "Street" => $auctionData->address ?? '',
    //                     "ZipCode" => "", 
    //                     "x" => (float) $auctionData->lng,  
    //                     "y" => (float) $auctionData->lat, 
    //                     "VisibilityModeId" => 2 
    //                 ]
    //             ],
    //             "PropertyFeature" => [
    //                 [
    //                     "FeatureId" => 1,  
    //                     "TextValue" => $auctionData->description ?? ''
    //                 ],
    //                 [
    //                     "FeatureId" => 2,  
    //                     "TextValue" => $auctionData->title ?? ''
    //                 ]
                    
    //             ],
    //             "PropertyTransaction" => [
    //                 [
    //                     "TransactionTypeId" => 1,  
    //                     "Price" => (float) $auctionData->minimum_bid, 
    //                     "ShowPrice" => true
    //                 ]
    //             ],
    //             "PropertyContactInfo" => [
    //                 [  
    //                   "TypeId" => 1, 
    //                   "Value" => "info@oportunalia.com" 
    //                 ],
    //                 [  
    //                   "TypeId" => 2, 
    //                   "Value" => "+34 911 254 530"
    //                 ]
    //             ],
    //             "PropertyDocument" => array_map(function ($image, $index) {
    //                 return [
    //                     'TypeId' => 1, 
    //                     'Url' => $image['path'] ?? '',
    //                     'SortingId' => $index + 1
    //                 ];
    //             }, $auctionData['images'] ?? [], array_keys($auctionData['images'] ?? []))
    //         ];
    
           
    //         $response = Http::withHeaders([
    //             'Api-Key' => config('services.fotocasa.api_key_test'),
    //             'Content-Type' => 'application/json',
    //             'Accept' => 'application/json'
    //         ])->post('https://imports.gw.fotocasa.pro/api/property', $fotocasaData);
    
           
    //         throw new \Exception("sincronizando auction con Fotocasa: ". $response->throw());
         
         
    
    //     } catch (\Exception $e) {
    //         throw new \Exception("Error sincronizando auction con Fotocasa: ".$e->getMessage());
           
           
    //     }
    // }

    public function syncWithFotocasa($auction) {
        try {
           
            $response = Http::get(url("api/auction/{$auction->link_rewrite}"));
            $response_active = Active::where('id', $auction->active_id)->first();
          
            
            /*logger()->debug('Datos del activo:', [
                'active' => $response_active->toArray()
            ]);*/
            
            $typeMapping = FotocasaMapper::mapCategory($response_active->active_category_id);
            if (!$response->successful()) {
                throw new \Exception("Error al obtener auction: ".$response->status());
            }
    
            $auctionData = $response->json()['response'];   
    
            $fotocasaData = [
                "ExternalId" => (string) $auctionData['id'],  
                "AgencyReference" => $auctionData['auto'] ?? "REF-".$auctionData['id'],
                'TypeId' => (int) $typeMapping['type'],
                "ContactTypeId" => 3,
                'SubTypeId' => $typeMapping['subtype'] ?? null,
                "PropertyAddress" => [
                    [
                        "Street" => $auctionData['address'] ?? 'Sin calle',
                        "x" => (float) $auctionData['lng'], 
                        "y" => (float) $auctionData['lat'], 
                        "Number" => "",
                        "VisibilityModeId" => 1
                    ]
                ],
                "PropertyFeature" => [
                    [
                        "FeatureId" => 3,
                        "TextValue" => $auctionData['description']/*$this->description*/ ?? 'Sin descripcion'
                    ],
                    [
                        "FeatureId" => 2,
                        "TextValue" => $auctionData['title'] ?? 'Sin titulo'
                    ],
                    [
                        "FeatureId" => 1,
                        "DecimalValue" => (int) $response_active['area'] ?? 0
                    ],[
                        "FeatureId" => 327,
                        "DecimalValue" => 1
                    ],[
                        "FeatureId" => 326,
                        "DecimalValue" => 1
                    ],[
                        "FeatureId" => 325,
                        "DecimalValue" => 1
                    ],[
                        "FeatureId" => 324,
                        "DecimalValue" => 1
                    ],[
                        "FeatureId" => 323,
                        "DecimalValue" => 1
                    ]
                ],
                "PropertyTransaction" => [
                    [
                        "TransactionTypeId" => 1,
                        "Price" => (float) ($auctionData['start_price'] ?? 0),
                        "ShowPrice" => true
                    ]
                ],
                "PropertyContactInfo" => [
                    [  
                        "TypeId" => 1, 
                        "Value" => "info@oportunalia.com" 
                    ],
                    [  
                        "TypeId" => 2, 
                        "Value" => "34911254530"
                    ]
                ],
                "PropertyDocument" => array_map(function ($image) use (&$index) {
                    return [
                        'TypeId' => 1, 
                        'Url' => $image['path'], 
                        'SortingId' => ++$index 
                    ];
                }, $auctionData['images'] ?? [])
            ];

            logger()->debug('Datos del activo:', [
                'activo' => $fotocasaData
            ]);

    
            $response = Http::withHeaders([
                'Api-Key' => "G921CBlEVogm16vF5DTWhQt8qtPg65Pac50ud7sdZRVPKqT1FNF8NLg9KOehnhKE",
                'X-Source' => "2af813dd-057a-4995-911a-0b4004ecbdd7",
                'Content-Type' => 'application/json'
            ])->post('https://imports.gw.fotocasa.pro/api/property', $fotocasaData);

            $response->throw(); 

            logger()->debug('Respuesta del activo:', [
                'respuesta' => $response->json()
            ]);
    
            return $response->json();
    
        } catch (\Exception $e) {
           
            throw new \Exception("Error al sincronizar: " . $e->getMessage());
        }
    }

    public function detail(Request $request, $id)
    {
        $notifs = Notification::where('auction_id',$id)->get();

        foreach ($notifs as $key => $value) {
            $value->notification_status = 1;
            $value->save();
        }
        $auction = Auction::with(["technicalArchive", "landRegistryArchive", "conditionsArchive", "descriptionArchive","technicalArchiveTwo", "landRegistryArchiveTwo", "conditionsArchiveTwo", "descriptionArchiveTwo"])
            ->selectraw(
                "auctions.*,
                Count(bids.id) as bids,
                auction_statuses.name as status,
                actives.name as active,
                auction_types.name as type,
                auctions.dontshowtimer,
                auctions.link_rewrite,
                auctions.meta_title,
                auctions.meta_description,
                auctions.meta_keywords
            ")
            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")
            ->join("actives", "auctions.active_id", "=", "actives.id")
            ->leftJoin("bids", "auctions.id", "=", "bids.auction_id")
            ->groupBy("auctions.id")
            ->where("auctions.id", "=", $id)
            ->first();

        $auction->favorites = Favorite::where("auction_id", $id)
            ->count();

        $auction->complete_deposits = Deposit::where("auction_id", $id)
            ->where("status", 1)
            ->count();

        if ($auction->auction_type_id == 1) {
            $auction->public_path = url('/subasta/'.$auction->link_rewrite);
        }else if ($auction->auction_type_id == 2) {
            $auction->public_path = url('/venta-directa/'.$auction->link_rewrite);
        }else if ($auction->auction_type_id == 3) {
            $auction->public_path = url('/cesion-de-remate/'.$auction->link_rewrite);
        }else if ($auction->auction_type_id == 4) {
            $auction->public_path = url('/cesion-de-credito/'.$auction->link_rewrite);
        }

        $this->response = AuctionResource::make($auction);
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    /**
     * Updates data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $auction = Auction::find($id);

        $dateNow = (new \DateTime())->format('Y-m-d H:i:s');

        $rules = [
            'title' => 'required|string',
            'active_id' => 'required',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s|after:'.$dateNow,
            'appraisal_value' => 'required|numeric',
            'start_price' => 'required|numeric',
            'deposit' => 'nullable|numeric',
            'minimum_bid' => 'nullable|numeric',
            'commission' => 'required|numeric',
            'bid_price_interval' => 'required|numeric',
            'bid_time_interval' => 'required|integer',
            'video' => 'nullable|string',
            'video_file' => 'nullable|string',
            'idealista' => 'nullable|string',
            'rrss' => 'nullable|string',
            'repercusion' => 'nullable|string',
            'mailing' => 'nullable|string',
            'auto' => 'nullable|string',
            'juzgado' => 'nullable|string',
            'description' => '',
            'land_registry' => '',
            'technical_specifications' => '',
            'conditions' => '',
            'auction_status_id' => '',
            'featured' => '',
            'asignado' => '',
            'dontshowtimer' => '',
            'link_rewrite' => 'required|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ];

        if (in_array($request->get('auction_status_id'), [AuctionStatus::FINISHED, AuctionStatus::ARCHIVED]))
        {
            $rules['end_date'] = 'required|date_format:Y-m-d H:i:s';
        }

        $validator = Validator::make($request->all(), $rules)
            ->after(function ($validator) use ($request, $auction) {

                if ($request->has('active_id')
                    && $request->get('active_id') != null
                    && $auction->active_id != $request->get('active_id')
                )
                {
                    $auctions = Auction::where('active_id', $request->get('active_id'))
                        ->whereIn('auction_status_id', [AuctionStatus::ONGOING, AuctionStatus::ARCHIVED])
                        ->count();

                    if ($auctions > 0) {
                        $validator->errors()->add('active_id', 'validation:used');
                    }
                }
            })
            ->after(function ($validator) use ($request,$id) {

                if ($request->has('link_rewrite') && $request->get('link_rewrite') != null)
                {
                    $auctions = Auction::where('link_rewrite', $request->get('link_rewrite'))->where('id', '!=' ,$id)
                        ->count();

                    if ($auctions > 0) {
                        $validator->errors()->add('link_rewrite', 'validation:used');
                    }
                }
            });

        $validator2 = Validator::make($request->all(), [
            'description_document' => 'nullable|mimes:pdf',
            'technical_document' => 'nullable|mimes:pdf',
            'land_registry_document' => 'nullable|mimes:pdf',
            'conditions_document' => 'nullable|mimes:pdf',
            'description_document_two' => 'nullable|mimes:pdf',
            'technical_document_two' => 'nullable|mimes:pdf',
            'land_registry_document_two' => 'nullable|mimes:pdf',
            'conditions_document_two' => 'nullable|mimes:pdf',
        ]);

        if ($validator->fails() || $validator2->fails())
        {
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            $this->messages[] = array_merge($validator->errors()->messages());
        }
        else
        {
            $values = $validator->validated();

            $dateNow = new \DateTime();
            $dateNow->setTime($dateNow->format('H'), $dateNow->format('i') - 5, 0);

            $dateStart = new \DateTime($values['start_date']);
            $dateEnd = new \DateTime($values['end_date']);

            switch ($values['auction_status_id'])
            {
                case AuctionStatus::SOON:
                    if ($dateStart <= $dateNow && $dateEnd > $dateNow) {
                        $values['auction_status_id'] = AuctionStatus::ONGOING;
                    }
                    break;

                case AuctionStatus::ONGOING:
                    if ($dateStart > $dateNow) {
                        $values['auction_status_id'] = AuctionStatus::SOON;
                    }
                    break;

                case AuctionStatus::FINISHED:
                    break;
                case AuctionStatus::DRAFT:
                    break;
                case AuctionStatus::ARCHIVED:
                    if ($dateStart > $dateNow) {
                        $values['auction_status_id'] = AuctionStatus::SOON;
                    } else if ($dateStart <= $dateNow && $dateEnd > $dateNow) {
                        $values['auction_status_id'] = AuctionStatus::ONGOING;
                    }
                    break;
                default:
                    if ($dateStart <= $dateNow && $dateEnd > $dateNow) {
                        $values['auction_status_id'] = AuctionStatus::ONGOING;
                    }
                    else if ($dateStart > $dateNow) {
                        $values['auction_status_id'] = AuctionStatus::SOON;
                    }
                    break;
            }

            if ($request->file("description_document")) {
                $fileDescription = Storage::disk("public")->put("", $request->file("description_document"));
                $archive = Archive::create(["name" => $request->file("description_document")->getClientOriginalName(), "path" => $fileDescription]);
                $archiveDeleteDescription = Archive::find($auction->description_archive_id);
                if ($archiveDeleteDescription) {
                    Storage::disk("public")->delete($archiveDeleteDescription->path);
                }
                $values = array_merge($values, ["description_archive_id" => $archive->id]);
            }
            if ($request->file("technical_document")) {
                $fileTechnical = Storage::disk("public")->put("", $request->file("technical_document"));
                $archive = Archive::create(["name" => $request->file("technical_document")->getClientOriginalName(), "path" => $fileTechnical]);
                $archiveDeleteTechnical = Archive::find($auction->technical_archive_id);
                if ($archiveDeleteTechnical) {
                    Storage::disk("public")->delete($archiveDeleteTechnical->path);
                }
                $values = array_merge($values, ["technical_archive_id" => $archive->id]);
            }
            if ($request->file("land_registry_document")) {
                $fileLandRegistry = Storage::disk("public")->put("", $request->file("land_registry_document"));
                $archive = Archive::create(["name" => $request->file("land_registry_document")->getClientOriginalName(), "path" => $fileLandRegistry]);
                $archiveDeleteLandRegistry = Archive::find($auction->technical_archive_id);
                if ($archiveDeleteLandRegistry) {
                    Storage::disk("public")->delete($archiveDeleteLandRegistry->path);
                }
                $values = array_merge($values, ["land_registry_archive_id" => $archive->id]);
            }
            if ($request->file("conditions_document")) {
                $fileConditions = Storage::disk("public")->put("", $request->file("conditions_document"));
                $archive = Archive::create(["name" => $request->file("conditions_document")->getClientOriginalName(), "path" => $fileConditions]);
                $archiveDeleteConditions = Archive::find($auction->conditions_archive_id);
                if ($archiveDeleteConditions) {
                    Storage::disk("public")->delete($archiveDeleteConditions->path);
                }
                $values = array_merge($values, ["conditions_archive_id" => $archive->id]);
            }


            if ($request->file("technical_document_two")) {
                $fileTechnical_two = Storage::disk("public")->put("", $request->file("technical_document_two"));
                $archive = Archive::create(["name" => $request->file("technical_document_two")->getClientOriginalName(), "path" => $fileTechnical_two]);
                $archiveDeleteTechnical_two = Archive::find($auction->technical_archive_two_id);
                if ($archiveDeleteTechnical_two) {
                    Storage::disk("public")->delete($archiveDeleteTechnical_two->path);
                }
                $values = array_merge($values, ["technical_archive_two_id" => $archive->id]);
            }
            if ($request->file("description_document_two")) {
                $fileDescription_two = Storage::disk("public")->put("", $request->file("description_document_two"));
                $archive = Archive::create(["name" => $request->file("description_document_two")->getClientOriginalName(), "path" => $fileDescription_two]);
                $archiveDeleteDescription_two = Archive::find($auction->description_archive_two_id);
                if ($archiveDeleteDescription_two) {
                    Storage::disk("public")->delete($archiveDeleteDescription_two->path);
                }
                $values = array_merge($values, ["description_archive_two_id" => $archive->id]);
            }
            if ($request->file("land_registry_document_two")) {
                $fileLandRegistry_two = Storage::disk("public")->put("", $request->file("land_registry_document_two"));
                $archive = Archive::create(["name" => $request->file("land_registry_document_two")->getClientOriginalName(), "path" => $fileLandRegistry_two]);
                $archiveDeleteLandRegistry_two = Archive::find($auction->land_registry_two_id);
                if ($archiveDeleteLandRegistry_two) {
                    Storage::disk("public")->delete($archiveDeleteLandRegistry_two->path);
                }
                $values = array_merge($values, ["land_registry_archive_two_id" => $archive->id]);
            }
            if ($request->file("conditions_document_two")) {
                $fileConditions_two = Storage::disk("public")->put("", $request->file("conditions_document_two"));
                $archive = Archive::create(["name" => $request->file("conditions_document_two")->getClientOriginalName(), "path" => $fileConditions_two]);
                $archiveDeleteConditions_two = Archive::find($auction->conditions_archive_two_id);
                if ($archiveDeleteConditions_two) {
                    Storage::disk("public")->delete($archiveDeleteConditions_two->path);
                }
                $values = array_merge($values, ["conditions_archive_two_id" => $archive->id]);
            }

            $auction->update($values);

            $this->code = ResponseAlias::HTTP_OK;

            if ($values['auction_status_id'] == AuctionStatus::ONGOING) {
                $this->syncWithFotocasa($auction);
            }else{
                $response = Http::withHeaders([
                    'Api-Key' => "G921CBlEVogm16vF5DTWhQt8qtPg65Pac50ud7sdZRVPKqT1FNF8NLg9KOehnhKE",
                    'X-Source' => "2af813dd-057a-4995-911a-0b4004ecbdd7",
                    'Content-Type' => 'application/json'
                ])->delete('https://imports.gw.fotocasa.pro/api/v2/property/'.base64_encode($auction['id']));

                \Log::info($response);
            }
        }
        return $this->sendResponse();
    }

    /**
     * Puts or removes an auction on featured.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function featured(Request $request, $id)
    {
        $auction = Auction::find($id);

        if (!$auction)
        {
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }

        $auction->featured = !$auction->featured;
        $auction->save();

        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }


        /**
     * Puts or removes an auction on asignado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function asignado(Request $request, $id)
    {
        $auction = Auction::find($id);

        if (!$auction)
        {
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }

        $auction->asignado = !$auction->asignado;
        $auction->save();

        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }


    /**
     * Deletes data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        try
        {
            $auction = Auction::find($id);

            if ($auction->auction_status_id != AuctionStatus::DRAFT)
            {
                $this->messages[] = 'Cannot delete auctions that are not in draft status';
                $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
                return $this->sendResponse();
            }

            $auction->delete();

            $response = Http::withHeaders([
                'Api-Key' => "G921CBlEVogm16vF5DTWhQt8qtPg65Pac50ud7sdZRVPKqT1FNF8NLg9KOehnhKE",
                'X-Source' => "2af813dd-057a-4995-911a-0b4004ecbdd7",
                'Content-Type' => 'application/json'
            ])->delete('https://imports.gw.fotocasa.pro/api/v2/property/'.base64_encode($auction['id']));
    
            // $response->throw(); 

            $this->code = ResponseAlias::HTTP_OK;

        } catch (\Illuminate\Database\QueryException $exception) {
            $this->code = ResponseAlias::HTTP_FORBIDDEN;
        }

        return $this->sendResponse();
    }

    public function bids(Request $request,$id)
    {
        $auctionQuery =  Bid::selectraw(
            "bids.id,
            users.id as user_id,
            users.username,
            roles.description as role,
            users.firstname,
            users.lastname,
            users.document_number,
            users.email,
            users.phone,
            users.address,
            users.city,
            provinces.name as province,
            bids.import"
        )
        ->join("auctions", "auctions.id", "=", "bids.auction_id")
        ->join("users", "bids.user_id", "=", "users.id")
        ->leftJoin("provinces", "provinces.id", "=", "users.province_id")
        ->join("roles", "users.role_id", "=", "roles.id")
        ->where("auctions.id", $id)
        ->orderBy("bids.import", "desc");

        $auctionQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('reference', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.username', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('role', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.firstname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.lastname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.document_number', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.email', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.phone', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.address', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.city', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('province', 'LIKE', '%' . $request->input('search') . '%');
        });

        $auctionQuery->when($request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $bids = $auctionQuery->get();

        if ($bids->count())
        {
            $winner = $bids->pull(0);
            $winner->is_best_bid = true;
            $bids->prepend($winner);
        }

        $this->response = $bids;
        $this->total = $bids->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    public function history(Request $request, $id)
    {
        $auctionQuery = Bid::select(
            "bids.id",
            "users.username",
            "bids.created_at",
            "bids.import"
        )
        ->join("users", "bids.user_id", "=", "users.id")
        ->join("roles", "users.role_id", "=", "roles.id")
        ->join("provinces", "users.province_id", "=", "provinces.id")
        ->where("auction_id", $id)
        ->orderBy("bids.import", "desc");

        $auction = Auction::selectraw(
            "auctions.id,
            auctions.title,Count(bids.id) as bids,
            Max(bids.import) as max_bid,
            auctions.auction_status_id,
            auctions.active_id,
            auctions.start_date,
            auctions.end_date"
        )
        ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
        ->leftJoin("bids", "auctions.id", "=", "bids.auction_id")
        ->where("auctions.id", $id)
        ->groupBy("auctions.id")->first();

        $bids = $auctionQuery->get();

        $auction = AuctionResourceWeb::make($auction);

        if ($bids->count())
        {
            $winner = $bids->pull(0);
            $winner->is_best_bid = true;
            $bids->prepend($winner);
        }

        $this->response = compact(['auction', 'bids']);
        $this->total = $bids->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    public function deposits($id, Request $request)
    {
        $depositsQuery = Deposit::with(["document"])->select(
            "deposits.id",
            "deposits.created_at",
            "deposits.status",
            "deposits.archive_id",
            "deposits.deposit",
            "users.username",
            "users.firstname",
            "users.lastname",
            "users.document_number",
        )
        ->join("auctions", "auctions.id", "=", "deposits.auction_id")
        ->join("users", "users.id", "=", "deposits.user_id")
        ->where("deposits.auction_id", $id)
        ->where("deposits.status", 0);

        $depositsQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->Where('users.firstname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.lastname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.document_number', 'LIKE', '%' . $request->input('search') . '%');

        });

        $depositsQuery->when($request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $deposits = $depositsQuery->get();

        $deposits = DepositResource::collection($deposits);

        $this->response = $deposits;
        $this->total = $deposits->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    /**
     * Returns auction activity.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function activity(Request $request, $id)
    {
        $auction = Auction::select(
            "auctions.id",
            "auctions.auction_type_id",
            "auctions.end_date",
            "auctions.title",
            "auctions.auction_status_id",
            DB::raw("
                CASE 
                    WHEN auctions.auction_type_id = 1 THEN 
                        (SELECT MAX(bids.import) FROM bids WHERE bids.auction_id = auctions.id) 
                    ELSE 
                        (SELECT MAX(direct_sale_offers.import) FROM direct_sale_offers WHERE direct_sale_offers.auction_id = auctions.id) 
                END as max_bid
                "),
            DB::raw("
                CASE 
                    WHEN auctions.auction_type_id = 1 THEN 
                        Count(bids.id)
                    ELSE 
                        Count(direct_sale_offers.id)
                END as bids
                "),
            )
            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")
            ->join("actives", "auctions.active_id", "=", "actives.id")

            ->leftJoin("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")
            ->leftJoin("bids", "auctions.id", "=", "bids.auction_id")

            ->groupBy("auctions.id")
            ->where("auctions.id", "=", $id)
            ->first();

        $notifications = Notification::select(
            "notifications.id",
            "notifications.title",
            "notifications.subtitle",
            "notifications.user_id",
            "notifications.created_at",
            "notifications.status",
            "notifications.type_id",
            "notifications.auction_id"
        )
        ->whereIn("type_id", [
            Notification::BID,
            Notification::AUCTION_END_WIN,
            Notification::AUCTION_END,
        ])
        ->where("notifications.auction_id", $id)
        ->orderby("notifications.id", "desc")
        ->get();

        foreach ($notifications as $notification)
        {
            // GMT+2 (Europe/Madrid) time correction
            $notification->created_at = date('Y-m-d H:i:s', strtotime($notification->created_at . ' +2 hour'));
        }

        if ($notifications->count())
        {
            $winner = $notifications->pull(0);
            $winner->is_best_bid = true;
            $notifications->prepend($winner);
        }

        $images = ActiveImages::with('image')
            ->join("actives", "active_images.active_id", "=", "actives.id")
            ->join("auctions", "auctions.active_id", "=", "actives.id")
            ->where("auctions.id", "=", $id)->get();

        $auction->images = ActiveImagesResource::collection($images);
        $auction->notifications = $notifications;

        $this->response = $auction;
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }


    public function duplicate(Request $request, $id)
    {
        $auction = Auction::find($id);
        $active = Active::find($auction->active_id);

        if($auction->auction_status_id == 1 ||$auction->auction_status_id == 5 ||$auction->auction_status_id == 7){
            $new_active = $active->replicate();
            $new_active_name = $new_active->name . " - copia Subasta";
            $new_active->name = $new_active_name;
            $new_active->created_at = Carbon::now();
            $new_active->updated_at = Carbon::now();
        }

        $new_auction = $auction->replicate();
        $new_auction->title = $new_auction->title . " - copia Subasta";
        $new_url = $new_auction->link_rewrite . "-".Str::random(10);
        $new_auction->link_rewrite = $new_url;
        $new_auction->guid = Str::uuid();
        $new_auction->created_at = Carbon::now();
        $new_auction->updated_at = Carbon::now();
        $new_auction->views=0;
        $new_auction->auction_status_id = 2;
        /*$new_auction->technical_archive_id = NULL;
        $new_auction->description_archive_id = NULL;
        $new_auction->land_registry_archive_id = NULL;
        $new_auction->conditions_archive_id = NULL;
        $new_auction->technical_archive_two_id = NULL;
        $new_auction->description_archive_two_id = NULL;
        $new_auction->land_registry_archive_two_id = NULL;
        $new_auction->conditions_archive_two_id = NULL;*/

        if($auction->auction_status_id == 1 ||$auction->auction_status_id == 5 ||$auction->auction_status_id == 7){
            $new_active->save();
        }

        $new_auction->save();

        /* Duplicamos imagenes */
        if($auction->auction_status_id == 1 ||$auction->auction_status_id == 5 ||$auction->auction_status_id == 7){
            $images = ActiveImages::with('image')->where("active_images.active_id", "=", $auction->active_id)->get();
            if(count($images)){
                foreach($images as $key=>$value){
                    $new_image = new ActiveImages();
                    $new_image->active_id = $new_active->id;
                    $new_image->created_at = Carbon::now();
                    $new_image->updated_at = Carbon::now();
                    $new_image->archive_id = $value->archive_id;
                    $new_image->save();
                }
            }
        }

        if($auction->auction_status_id == 1 ||$auction->auction_status_id == 5 ||$auction->auction_status_id == 7){
            $new_active = Active::where("actives.name", $new_active_name)->first();

            Auction::where("auctions.link_rewrite", $new_url)->first()->update(array('active_id' => $new_active->id));

            $this->response = $new_active;
        }else{
            $old_active = Active::where("actives.name", $active->name)->first();

            Auction::where("auctions.link_rewrite", $new_url)->first()->update(array('active_id' => $old_active->id));

            $this->response = $old_active;

        }

        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();

    }

    /**
     * Export the resource to a file.
     *
     * @param  string  $type
     * @return any
     */
    public function export($type)
    {
        $filename = [
            config('app.name'),
            __('fields.backoffice.auctions'),
        ];

        switch ($type)
        {
            case 'xlsx':

                $filename = composeFilename($filename, 'xlsx');

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\Admin\AuctionExport, $filename
                );
                break;

            default:
                $this->code = ResponseAlias::HTTP_BAD_REQUEST;
                return $this->sendResponse();
                break;
        }
    }

    /**
     * Generates the final report of an auction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function report($id)
    {
        set_time_limit(300);
        ini_set('memory_limit', '256M');

        $auction = Auction::find($id);

        if (!$auction)
        {
            $this->code = ResponseAlias::HTTP_BAD_REQUEST;
            return $this->sendResponse();
        }

        $images = AuctionResourceWeb::make($auction)->toArray(null);

        $auction->images = $images['images']->toArray(null);

        $bids = Bid::with(['user','representation'])->where("auction_id", $auction->id)
            ->orderBy("import", "desc")
            ->get();

        $auction->bids = $bids;
        $auction->last_bid = $bids->count() ? $bids[0] : null;
        $auction->commission_import = ($auction->last_bid ? $auction->last_bid->import : 0) * ($auction->commission / 100);

        $auction->bids = $bids;

        $pdf = PDF::loadView('pdf.auction-end', [
            'product' => $auction,
        ]);

        $filename = [
            config('app.name'),
            __('fields.backoffice.cesion_final_report'),
            $auction->id,
        ];

        $filename = composeFilename($filename, 'pdf');

        return $pdf->stream($filename);
    }


    /**
     * Generates the final report of a direct sale.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function direct_sale_report($id)
    {
        set_time_limit(300);
        ini_set('memory_limit', '256M');

        $auction = Auction::find($id);
        $direct_offer = DirectSaleOffer::with(["auction"])->find($id);

        if (!$auction)
        {
            $this->code = ResponseAlias::HTTP_BAD_REQUEST;
            return $this->sendResponse();
        }

        $images = AuctionResourceWeb::make($auction)->toArray(null);

        $auction->images = $images['images']->toArray(null);

        $bids = DirectSaleOffer::with(["auction"])->where("auction_id", $auction->id)
            ->orderBy("import", "desc")
            ->get();

        $auction->bids = $bids;
        $auction->last_bid = $bids->count() ? $bids[0] : null;
        $auction->commission_import = ($auction->last_bid ? $auction->last_bid->import : 0) * ($auction->commission / 100);

        $auction->bids = $bids;

        $pdf = PDF::loadView('pdf.direct_sale_auction-end', [
            'product' => $auction,
        ]);

        $filename = [
            config('app.name'),
            __('fields.backoffice.auction_final_report'),
            $auction->id,
        ];

        $filename = composeFilename($filename, 'pdf');

        return $pdf->stream($filename);
    }

    /**
     * Generates the final report of a cesion.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cesion_report($id)
    {
        set_time_limit(300);
        ini_set('memory_limit', '256M');

        $auction = Auction::find($id);
        $cesion = DirectSaleOffer::with(["auction"])->find($id);

        if (!$auction)
        {
            $this->code = ResponseAlias::HTTP_BAD_REQUEST;
            return $this->sendResponse();
        }

        $images = AuctionResourceWeb::make($auction)->toArray(null);

        $auction->images = $images['images']->toArray(null);

        $bids = DirectSaleOffer::with(["auction"])->where("auction_id", $auction->id)
            ->orderBy("import", "desc")
            ->get();

        $auction->bids = $bids;
        $auction->last_bid = $bids->count() ? $bids[0] : null;
        $auction->commission_import = ($auction->last_bid ? $auction->last_bid->import : 0) * ($auction->commission / 100);

        $auction->bids = $bids;

        $pdf = PDF::loadView('pdf.cesion_auction-end', [
            'product' => $auction,
        ]);

        $filename = [
            config('app.name'),
            __('fields.backoffice.auction_final_report'),
            $auction->id,
        ];

        $filename = composeFilename($filename, 'pdf');

        return $pdf->stream($filename);
    }


    public function deleteDocument(Request $request, $id)
    {
        $document_to_delete = "";

        if($request->get('document')=="technical_one"){ $document_to_delete = "technical_archive_id";}
        if($request->get('document')=="technical_two"){ $document_to_delete = "technical_archive_two_id";}

        if($request->get('document')=="description_one"){ $document_to_delete = "description_archive_id";}
        if($request->get('document')=="description_two"){ $document_to_delete = "description_archive_two_id";}

        if($request->get('document')=="land_registry_one"){ $document_to_delete = "land_registry_archive_id";}
        if($request->get('document')=="land_registry_two"){ $document_to_delete = "land_registry_archive_two_id";}

        if($request->get('document')=="conditions_one"){ $document_to_delete = "conditions_archive_id";}
        if($request->get('document')=="conditions_two"){ $document_to_delete = "conditions_archive_two_id";}

        Auction::where("id", $id)->update(array($document_to_delete => NULL));
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();
    }

    public function importAuctions(){
        return view('file-upload');
    }

    public function repeatAuctions(){
        return view('repeat-upload');
    }

    public function storeActives(Request $request)
    {
        $validtedData = Validator::make($request->all(), [
            'file' => 'required|xlsx|max:2048',
        ]);

        $name = $request->file('file')->getClientOriginalName();

        if ($name == "act6597d80285c116597d80285c15.xlsx") {

            $path = $request->file('file')->store('public/files');

            Excel::import(new ActivesImport, $path);

            return view('file-upload')->with('message', 'Se han creado los activos');

        } else {

            return view('file-upload')->with('error', 'El archivo no es correcto');
        }

    }

    public function storeAuctions(Request $request)
    {
        $validtedData = Validator::make($request->all(), [
            'file' => 'required|xlsx|max:2048',
        ]);

        $name = $request->file('file')->getClientOriginalName();


        if ($name == "repeat659e8ae6668dd659e8ae6668df.xlsx") {


            $path = $request->file('file')->store('public/files');

            Excel::import(new AuctionsImport($request->type ? $request->type : 'cesion'), $path);

            return view('repeat-upload')->with('message', 'Se han creado las ventas');

        } else {

            return view('repeat-upload')->with('error', 'El archivo no es correcto');
        }

    }

    public function uploadFiles(Request $request){
        if($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $file->storeAs('uploads', $filename, 'public');

            $file = fopen('documents/uploads/importactives'.date('d-m-y').'.log', 'a');
            fwrite($file, date("d/m/Y H:i:s").'-'.'Archivo subido correctamente: '.$filename.PHP_EOL);
            fclose($file);
        }

        return response()->json(['message' => 'Archivo subido con éxito.'], 200);
    }

    public function testPipe($id){
        /*
        $guid = 'vivienda-en-placeta-oate---finca-registral-30856-2-IKuI4sMzDY-QOlzhRNUAx-5YdW7XWR3B-uHOukv5a01-8dw9drUhjG';
        $auction = Auction::where('link_rewrite', $guid)->first();

        dump($auction);
        dump($auction->active_id);
        dump("active City");
        dump($auction->activo);
        dump($auction->activo->city);

        dump("active category");
        dump($auction->activo->active_category);
        dump("active category name");
        dump($auction->activo->active_category->name);

        dump("active province");
        dump($auction->activo->province);
        dump("active province name");
        dump($auction->activo->province->name);


        dd();*/
        /*
        $responsePerson = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->get(config('pipedrive.pipeurl').'persons/'.$id.'?api_token='.config('pipedrive.pitk'))->throw()->json();


        dump($responsePerson);
        dump($responsePerson['success']);
        dump($responsePerson['data']);
        dump($responsePerson['data']['label']);*/


        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->get(config('pipedrive.pipeurl').'deals/search?term=Usuario&person_id='.$id.'&api_token='.config('pipedrive.pitk'))->throw()->json();

        /* Testing call persons */
        /**/
            /* Testing call deals */
        //dump($response['data']['items'][0]['item']['id']);
        if(!empty($response['data']['items'])){
                dump("Hay resultados");
                dump(count($response['data']['items']));
                dump("Lead id: ");
                dump($response['data']['items'][0]['item']['id']);
                dump($response['data']);
                /*  Falla, json_decode(): Argument #1 ($json) must be of type string, array given
                $result = json_decode($response, true);
                dump($result);
                if ($result['data']) {
                    dump($result['data']);
                } */
            }else{
                dump("No hay resultados");
                dump(count($response['data']['items']));
                dump($response);
            }

        dd("Fin");


        return view('repeat-upload');
    }

    public function registerCampaign(Request $request)
    {
        if(file_exists('testing/sorteo.txt')){
            $file = fopen('testing/sorteo.log', 'a');
            fwrite($file, date("d/m/Y H:i:s").'-'.'Formulario de contacto registro sorteo '.PHP_EOL);
            fclose($file);
        }
        dd("Register Campaign from Auction Controller");
        /* $validator = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|max:100',
            'registercode' => 'required',
        ]);


        if ($validator->fails()){
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;

        }else{

            if(file_exists('testing/sorteo.txt')){
                $file = fopen('testing/sorteo.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'Formulario de contacto registro sorteo '.PHP_EOL);
                fclose($file);
            }
        } */

        return $this->sendResponse();
    }

    public function auctionsImport(Request $request)
    {
        $validtedData = Validator::make($request->all(), [
            'file' => 'required|xlsx|max:2048',
        ]);

        $name = $request->file('file')->getClientOriginalName();

        $path = $request->file('file')->store('public/files');

        Excel::import(new AuctionsImport($request->type ? $request->type : 'cesion'), $path);

        return response()->json(['message' => 'Archivo subido con éxito.'], 200);

        // return view('repeat-upload')->with('message', 'Se han creado las ventas');
        // Excel::import(new AuctionsImport, request()->file('file'));
    }

    public function activesImport(Request $request)
    {
        $validtedData = Validator::make($request->all(), [
            'file' => 'required|xlsx|max:2048',
        ]);

        $name = $request->file('file')->getClientOriginalName();

        $path = $request->file('file')->store('public/files');

        Excel::import(new ActivesImport, $path);

        return response()->json(['message' => 'Archivo subido con éxito.'], 200);

        // return view('file-upload')->with('message', 'Se han creado los activos');
        // Excel::import(new ActivesImport, request()->file('file'));
    }
}