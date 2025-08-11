<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Services\FotocasaMapper;
use App\Http\Resources\AuctionResourceWeb;
use App\Http\Resources\AuctionResource;
use App\Http\Resources\DepositResource;
use App\Mail\OfferAccepted;
use App\Mail\OfferRejected;
use App\Models\Archive;
use App\Models\Auction;
use App\Models\AuctionType;
use App\Models\AuctionStatus;
use App\Models\DirectSaleOffer;
use App\Models\Favorite;
use App\Models\User;
use App\Models\Notification;
use App\Models\Deposit;
use App\Models\Active;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

use App\Models\ActiveImages;

class CesionController extends ApiController
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
     * Returns a list of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listAll(Request $request)
    {
        $auctionQuery = Auction::selectraw(
            "auctions.id,
            auctions.title,
            Count(direct_sale_offers.id) as offers,
            Max(direct_sale_offers.import) as max_offer,
            auctions.start_date,
            auctions.end_date,
            auctions.deposit,
            auctions.start_price,
            auctions.appraisal_value,
            auctions.auction_status_id,
            auctions.featured,
            auctions.asignado,
            auctions.dontshowtimer,
            auctions.link_rewrite,
            auction_statuses.name as status,
            auctions.auto,
            actives.city",
        )
        ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
        ->join("actives", "auctions.active_id", "=", "actives.id")
        ->leftJoin("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")
        ->where("auction_type_id", 3)
        ->groupBy("auctions.id");

        $auctionQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('id', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('auto', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('title', 'LIKE', '%' . $request->input('search') . '%');
        });

        $auctionQuery->when($request->has('status_id'), function (Builder $builder) use ($request) {
            $builder->where('auctions.auction_status_id', '=', $request->input('status_id'));
        });

        $auctionQuery->when($request->has('start_date'), function (Builder $builder) use ($request) {
            $builder->where('auctions.start_date', '>=', $request->input('start_date'));
        });

        $auctionQuery->when($request->has('end_date'), function (Builder $builder) use ($request) {
            $builder->where('auctions.end_date', '<=', $request->input('end_date'));
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

    /**
     * Stores data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'active_id' => 'required',
            'auction_status_id' => 'required',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s',
            'appraisal_value' => 'required|numeric',
            'start_price' => 'required|numeric',
            'deposit' => 'nullable|numeric',
            'minimum_bid' => 'nullable|numeric',
            'commission' => 'required|numeric',
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
            'link_rewrite' => 'required|string',
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
            $conditions_archive;
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
            $conditions_archive = $conditions_archive->id;
            }else{ $conditions_archive=NULL; }


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

            $auction = Auction::create(array_merge($validator->validated(),
                [
                    'auction_status_id' => $values['auction_status_id'],
                    'guid' => (string) Str::uuid(),
                    'auction_type_id' => AuctionType::CESION,
                    'technical_archive_id' => $technical_archive_id,
                    'description_archive_id' => $description_archive_id,
                    'land_registry_archive_id' => $land_registry_archive_id,
                    'conditions_archive_id' => $conditions_archive,
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
     * Retrieves data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request, $id)
    {
        $auction = Auction::with([
            "technicalArchive",
            "landRegistryArchive",
            "conditionsArchive",
            "descriptionArchive",
            "technicalArchiveTwo",
            "landRegistryArchiveTwo",
            "conditionsArchiveTwo",
            "descriptionArchiveTwo"
        ])->selectraw(
            "auctions.id,
            auctions.title,
            auctions.start_date,auctions.end_date,
            auctions.created_at,
            Count(direct_sale_offers.id) as offers,
            auctions.active_id,
            auctions.commission,
            auctions.description,
            auctions.land_registry,
            auctions.technical_specifications,
            auctions.conditions,
            auctions.technical_archive_id,
            auctions.description_archive_id,
            auctions.land_registry_archive_id,
            auctions.conditions_archive_id,
            auctions.description_archive_two_id,
            auctions.technical_archive_two_id,
            auctions.land_registry_archive_two_id,
            auctions.conditions_archive_two_id,
            auctions.views,
            auctions.featured,
            auctions.asignado,
            auctions.dontshowtimer,
            auctions.link_rewrite,
            auctions.start_price,
            auctions.minimum_bid,
            auctions.appraisal_value,
            auctions.auction_status_id,
            auction_statuses.name as status"
        )
        ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
        ->leftJoin("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")
        ->where("auctions.id", "=", $id)
        ->groupBy("auctions.id")
        ->first();

        $auction->favorites = Favorite::where("auction_id","=",$id)->count();

        $auction->complete_deposits = Deposit::where("auction_id", $id)
        ->where("status", 1)
        ->count();

        $auction->public_path = url('/subasta/'.$auction->link_rewrite);

        $this->response = AuctionResource::make($auction);
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

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
            'auction_status_id' => '',
            'title' => 'required|string',
            'active_id' => 'required',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s|after:'.$dateNow,
            'appraisal_value' => 'required|numeric',
            'start_price' => 'required|numeric',
            'deposit' => 'nullable|numeric',
            'minimum_bid' => 'nullable|numeric',
            'commission' => 'required|numeric',
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
            'link_rewrite' => 'required|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ];

        if (in_array($request->get('auction_status_id'), [AuctionStatus::FINISHED, AuctionStatus::SOLD]))
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
                        ->whereIn('auction_status_id', [AuctionStatus::ONGOING, AuctionStatus::SOLD])
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

        //if ($validator->fails() || $validator2->fails())
        /*if ($validator->fails())
        {
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            $this->messages[] = array_merge($validator->errors()->messages());
        }
        else
        {*/
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
                if($archiveDeleteDescription){
                    Storage::disk("public")->delete($archiveDeleteDescription->path);
                }
                $values = array_merge($values, ["description_archive_id" => $archive->id]);
            }
            if ($request->file("technical_document")) {
                $fileTechnical = Storage::disk("public")->put("", $request->file("technical_document"));
                $archive = Archive::create(["name" => $request->file("technical_document")->getClientOriginalName(), "path" => $fileTechnical]);
                $archiveDeleteTechnical = Archive::find($auction->technical_archive_id);
                if($archiveDeleteTechnical){
                    Storage::disk("public")->delete($archiveDeleteTechnical->path);
                }
                $values = array_merge($values, ["technical_archive_id" => $archive->id]);
            }
            if ($request->file("land_registry_document")) {
                $fileLandRegistry = Storage::disk("public")->put("", $request->file("land_registry_document"));
                $archive = Archive::create(["name" => $request->file("land_registry_document")->getClientOriginalName(), "path" => $fileLandRegistry]);
                $archiveDeleteLandRegistry = Archive::find($auction->technical_archive_id);
                if($archiveDeleteLandRegistry){
                    Storage::disk("public")->delete($archiveDeleteLandRegistry->path);
                }
                $values = array_merge($values, ["land_registry_archive_id" => $archive->id]);
            }
            if ($request->file("conditions_document")) {
                $fileConditions = Storage::disk("public")->put("", $request->file("conditions_document"));
                $archive = Archive::create(["name" => $request->file("conditions_document")->getClientOriginalName(), "path" => $fileConditions]);
                $archiveDeleteConditions = Archive::find($auction->conditions_archive_id);
                if($archiveDeleteConditions){
                    Storage::disk("public")->delete($archiveDeleteConditions->path);
                }
                $values = array_merge($values, ["conditions_archive_id" => $archive->id]);
            }


            if ($request->file("technical_document_two")) {
                $fileTechnical_two = Storage::disk("public")->put("", $request->file("technical_document_two"));
                $archive = Archive::create(["name" => $request->file("technical_document_two")->getClientOriginalName(), "path" => $fileTechnical_two]);
                $archiveDeleteTechnical_two = Archive::find($auction->technical_archive_two_id);
                if ($archiveDeleteTechnical_two){
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

            return response()->json($auction,422);

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
        //}

        return $this->sendResponse();
    }

    /**
     * Returns a list of users of the direct sale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function listUsers(Request $request, $id)
    {
        $auctionQuery = DirectSaleOffer::select(
            "users.id as user_id",
            "direct_sale_offers.id",
            "users.username",
            "users.firstname",
            "users.lastname",
            "users.document_number",
            "users.email",
            "users.phone",
            "users.address",
            "users.city",
            "provinces.name as province",
            "roles.description as role",
            "direct_sale_offers.import",
            "direct_sale_offers.status"
        )
        ->join("users", "direct_sale_offers.user_id", "=", "users.id")
        ->join("roles", "users.role_id", "=", "roles.id")
        ->leftJoin("provinces", "users.province_id", "=", "provinces.id")
        ->where("auction_id", $id);

        $auctionQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('id', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.username', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.firstname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.document_number', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.email', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('roles.description', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.phone', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.address', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.city', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('provinces.name', 'LIKE', '%' . $request->input('search') . '%');
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

    public function history(Request $request, $id)
    {
        $auctionQuery = DirectSaleOffer::select(
            "direct_sale_offers.id",
            "users.username",
            "direct_sale_offers.created_at",
            "direct_sale_offers.status",
            "direct_sale_offers.import"
        )
        ->join("users", "direct_sale_offers.user_id", "=", "users.id")
        ->join("roles", "users.role_id", "=", "roles.id")
        ->leftJoin("provinces", "users.province_id", "=", "provinces.id")
        ->where("auction_id", "=", $id);

        $auction = Auction::selectraw("
            auctions.id,
            auctions.title,
            auctions.auction_status_id,
            auctions.active_id,
            auctions.start_date,
            auctions.end_date,
            Count(direct_sale_offers.id) as bids,
            Max(direct_sale_offers.import) as max_bid"
        )
        ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
        ->leftJoin("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")
        ->where("auctions.id", "=", $id)
        ->groupBy("auctions.id")
        ->first();

        $offers = $auctionQuery->get();

        $this->response = [
            "auction" => AuctionResourceWeb::make($auction),
            "offers" => $offers,
        ];
        $this->total = $offers->count();
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
     * Accepting or rejecting an offer
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function validate(Request $request, $id)
    {
        $direct_offer = DirectSaleOffer::with(["auction"])->find($id);

        $direct_offer->status = $request->get("status");
        $direct_offer->save();

        if ($request->get("status") == 1)
        {
            $offers = DirectSaleOffer::with(["auction"])
                ->where("auction_id", $direct_offer->auction_id)
                ->whereKeyNot([$direct_offer->id])
                ->get();

            foreach ($offers as $offer)
            {
                $offer->status = 2;
                $offer->save();
                $this->sendEmailOfferRejected($offer);
            }

            $auction = Auction::find($direct_offer->auction_id);
            $auction->auction_status_id = AuctionStatus::SOLD;
            $auction->save();

            $direct_offer->parseForEmail();
            $direct_offer->auction->parseForEmail();

            $this->sendEmailOfferAccepted($direct_offer);

            $user = User::find($direct_offer->user_id);

            Notification::create([
                'title' => __('notifications.direct_sale_end_win.title', [
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                ]),
                'subtitle' => __('notifications.direct_sale_end_win.subtitle', [
                    'import' => $direct_offer->auction->lastOffer,
                ]),
                'user_id' => $direct_offer->user_id,
                'auction_id' => $direct_offer->auction->id,
                'type_id' => Notification::AUCTION_END_WIN,
            ]);

        }

        if ($request->get("status") == 2)
        {
            $direct_offer->parseForEmail();
            $direct_offer->auction->parseForEmail();

            $this->sendEmailOfferRejected($direct_offer);
        }

        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();
    }

    private function sendEmailOfferAccepted($direct_offer)
    {
        $user = User::find($direct_offer->user_id);
        Mail::to($user->email)
            ->send(new OfferAccepted($user, $direct_offer));
    }

    private function sendEmailOfferRejected($direct_offer)
    {
        $user = User::find($direct_offer->user_id);
        Mail::to($user->email)
            ->send(new OfferRejected($user, $direct_offer));
    }


    public function duplicate(Request $request, $id)
    {
        $auction = Auction::find($id);
        $active = Active::find($auction->active_id);

        if($auction->auction_status_id == 1 ||$auction->auction_status_id == 5 ||$auction->auction_status_id == 7){
            $new_active = $active->replicate();
            $new_active_name = $new_active->name . " - copia Cesion de remate";
            $new_active->name = $new_active_name;
            $new_active->created_at = Carbon::now();
            $new_active->updated_at = Carbon::now();
        }

        $new_auction = $auction->replicate();
        $new_auction->title = $new_auction->title . " - copia Cesion de remate";
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
            __('fields.backoffice.cesions'),
        ];

        switch ($type)
        {
            case 'xlsx':
                $filename = composeFilename($filename, 'xlsx');

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\Admin\CesionExport, $filename
                );

            case 'offerscesions':
                $filename = composeFilename($filename, 'xlsx');

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\Admin\CesionOfferExport, $filename
                    );

            default:
                $this->code = ResponseAlias::HTTP_BAD_REQUEST;
                return $this->sendResponse();
        }
    }

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
                        "TextValue" => $this->description ?? 'Sin descripcion'
                    ],
                    [
                        "FeatureId" => 2,
                        "TextValue" => $auctionData['title'] ?? 'Sin titulo'
                    ],
                    [
                        "FeatureId" => 1,
                        "DecimalValue" => $response_active['area'] ?? 0
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

}
