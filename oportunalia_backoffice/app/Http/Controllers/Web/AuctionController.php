<?php



namespace App\Http\Controllers\Web;



use App\Http\Controllers\ApiController;

use App\Http\Resources\AuctionResource;

use App\Http\Resources\AuctionResourceWeb;

use App\Models\Archive;

use App\Models\Auction;

use App\Models\Active;

use App\Models\AuctionStatus;

use App\Models\Bid;

use App\Models\Deposit;

use App\Models\DirectSaleOffer;

use App\Models\Favorite;

use App\Models\Notification;

use App\Models\User;

use App\Models\Membresia;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;

use Symfony\Component\HttpFoundation\Response as ResponseAlias;

use Carbon\Carbon;

use Illuminate\Database\Query\JoinClause;



use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;



class AuctionController extends ApiController

{



    public function listAll(Request $request)

    {

        Schema::table('actives', function (Blueprint $table) {

            if (!Schema::hasColumn('actives', 'lat')) {

                $table->string('lat')->nullable();

            }

            if (!Schema::hasColumn('actives', 'lng')) {

                $table->string('lng')->nullable();

            }

        });

        // return $request->all();

        $auctionQuery = Auction::with(['bids', 'last_bid', 'offers', 'last_offer'])

        ->selectraw(

            "auctions.id,

                actives.active_category_id,

                auctions.guid,

                auctions.title,

                Max(bids.import) as max_bid,

                Max(direct_sale_offers.import) as max_offer,

                auctions.active_id,

                auctions.auction_type_id,

                auctions.start_date,

                auctions.appraisal_value,

                auctions.end_date,

                auctions.start_price,

                auctions.auction_status_id,

                auction_statuses.name as status,

                provinces.name as province,

                auction_types.name as type,

                auctions.dontshowtimer,

                auctions.link_rewrite,

                auctions.auto,

                auctions.created_at as published,

                auctions.views,

                actives.city,

                actives.address,

                actives.lat,

                actives.lng,

                auctions.repercusion,

                FORMAT((((auctions.appraisal_value - auctions.start_price)*100) / auctions.appraisal_value),2) as discount",



        )

            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")

            ->join("actives", "auctions.active_id", "=", "actives.id")

            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")

            ->join("provinces", "actives.province_id", "=", "provinces.id")

            ->groupBy("auctions.id");



        $auctionQuery->when($request->input('interacted', false), function (Builder $builder) use ($request) {



            $builder->leftJoin("bids", "auctions.id", "=", "bids.auction_id")

            ->leftJoin("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")

            ->havingRaw('Count(bids.id) > 0 OR Count(direct_sale_offers.id) > 0')

            ->where('bids.user_id', Auth::id())

                ->orWhere('direct_sale_offers.user_id', Auth::id());

                

        }, function (Builder $builder) use ($request) {



            $builder->when($request->input('bidded', null), function (Builder $builder) use ($request) {

                $builder->join("bids", "auctions.id", "=", "bids.auction_id")

                ->where('bids.user_id', Auth::id());

            }, function (Builder $builder) use ($request) {

                $builder->leftJoin("bids", "auctions.id", "=", "bids.auction_id");

            });



            $builder->when($request->input('offered', null), function (Builder $builder) use ($request) {

                $builder->join("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")

                ->where('direct_sale_offers.user_id', Auth::id());

            }, function (Builder $builder) use ($request) {

                $builder->leftJoin("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id");

            });

        });



        $auctionQuery->when($request->input('search', null), function (Builder $builder) use ($request) {

            $builder->where(function ($query) use ($request) {

                $query->where('provinces.name', 'LIKE', '%' . $request->input('search') . '%')

                      ->orWhere('title', 'LIKE', '%' . $request->input('search') . '%');

            });

        });



         $auctionQuery->when($request->input('favorites', false), function (Builder $builder) use ($request) {

            $builder->join("favorites", "auctions.id", "=", "favorites.auction_id")

            ->where('favorites.user_id', Auth::id());

        });



        /*$auctionQuery->when($request->input('auction_type_id', null), function (Builder $builder) use ($request) {

            $builder->where('auctions.auction_type_id', '=', $request->input('auction_type_id'));

        });*/



        $auctionQuery->when($request->input('auction_type_id', null), function (Builder $builder) use ($request) {

            $builder->where('auctions.auction_type_id', $request->input('auction_type_id'));

        }, function (Builder $builder) use ($request) {

            $builder->whereIn("auctions.auction_type_id", [1,2,3]);

        });



        if ($request->input('min', null) && !$request->input('max', null)) {

            $auctionQuery->where('start_price','>=',$request->input('min'));

        }



        if (!$request->input('min', null) && $request->input('max', null)) {

            $auctionQuery->where('start_price','<?',$request->input('max'));

        }



        if ($request->input('min', null) && $request->input('max', null)) {

            $auctionQuery->whereBetween('start_price',[$request->input('min'),$request->input('max')]);

        }





        $auctionQuery->when($request->input('active_category_id', null), function (Builder $builder) use ($request) {

            $builder->where('actives.active_category_id', '=', $request->input('active_category_id'));

        });



        $auctionQuery->when($request->input('start_date', null), function (Builder $builder) use ($request) {

            $builder->where('auctions.start_date', '>=', $request->input('start_date'));

        });



        $auctionQuery->when($request->input('end_date', null), function (Builder $builder) use ($request) {

            $builder->where('auctions.end_date', '<=', $request->input('end_date'));

        });



         $auctionQuery->when($request->input('featured', null), function (Builder $builder) use ($request) {

            $builder->where('auctions.featured', 1);

        });



        $auctionQuery->when($request->input('featured', null), function (Builder $builder) use ($request) {

            $builder->whereIn('auctions.auction_status_id', [

                AuctionStatus::ONGOING,

                AuctionStatus::SOON

            ]);

        });



         $auctionQuery->when($request->input('repercution', null), function (Builder $builder) use ($request) {

            $builder->where('auctions.repercusion', 'LIKE', $request->input('repercution'));

        });



        $auctionQuery->when($request->input('repercution', null), function (Builder $builder) use ($request) {

            $builder->whereIn('auctions.auction_status_id', [

                AuctionStatus::ONGOING,

                AuctionStatus::SOON

            ]);

        });



        $auctionQuery->when($request->input('order', null), function (Builder $builder) use ($request) {

            if ($request->input('auction_status_id') == "3") {

                $sent_order = explode("__", $request->input('order'))[1];

                if ($sent_order == "desc") {

                    $builder->orderBy(explode("__", $request->input('order'))[0], "asc");

                } else {

                    $builder->orderBy(explode("__", $request->input('order'))[0], "desc");

                }

            } else {

                $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);

            }

        });



        $auctionQuery->orderByRaw('FIELD(auctions.auction_status_id,1,2,3,4,5,6,7)');



        $auctionQuery->when($request->input('auction_status_id', null), function (Builder $builder) use ($request) {

            $builder->whereIn('auctions.auction_status_id', explode(',', $request->input('auction_status_id')));

        }, function (Builder $builder) use ($request) {

            $builder->whereIn("auctions.auction_status_id", [

                AuctionStatus::ONGOING,

                //AuctionStatus::FINISHED,

                AuctionStatus::SOLD,

                //AuctionStatus::UNSOLD,

                AuctionStatus::SOON,

                AuctionStatus::ARCHIVED,

            ]);

        });



        $auctions = $auctionQuery->get();



        foreach ($auctions as $i => $auction) {

            $auction->total_bids = count($auction->bids);

            $auction->total_offers = 0;

            $all_offers = $auction->offers;

            foreach ($all_offers as $offer) {

                if ($offer->status != 2) {

                    $auction->total_offers++;

                }

            }

            //unset($auction->id);

            unset($auction->bids);

            unset($auction->offers);



            $auction->user_is_winner = Auth::id() && $auction->last_bid && $auction->last_bid->user_id == Auth::id();



            if($auction->start_price!=0){

                $auction->discount = round((100-(($auction->start_price*100)/$auction->appraisal_value)) ,0);

            }

            if ($auction->auction_status_id == AuctionStatus::SOON) {

                $auction->seconds_to_end = Carbon::parse($auction->start_date)->diffInSeconds(Carbon::now()->toDateTimeString()) * 1000;

            } else {

                // FIX 2022-06-20: end_date might be past, avoid absolute values from diffInSeconds

                // $auction->seconds_to_end = Carbon::parse($auction->end_date)->diffInSeconds(Carbon::now()->toDateTimeString())*1000;

                $diff = Carbon::now()->diffInSeconds(Carbon::parse($auction->end_date), false);

                $auction->seconds_to_end = ($diff > 0) ? $diff * 1000 : 0;

            }

        }



        list($ongoing, $soon, $unsold, $archived, $sold, $draft) = array(array(),array(),array(),array(),array(),array());

        //ONGOING,SOON,UNSOLD,ARCHIVED,SOLD,DRAFT



        foreach($auctions as $auction){

            switch($auction->auction_status_id){

                case AuctionStatus::ONGOING:

                    array_push($ongoing,$auction);

                    break;

                /*case AuctionStatus::DRAFT:



                    array_push($draft,$auction);



                    break;*/

                /*case AuctionStatus::FINISHED:

                    array_push($finished,$auction);

                    break;*/

                case AuctionStatus::ARCHIVED:

                    array_push($archived,$auction);

                    break;

                case AuctionStatus::SOLD:

                    array_push($sold,$auction);

                    break;

                case AuctionStatus::SOON:

                    array_push($soon,$auction);

                    break;

                /* default:

                    array_push($unsold,$auction);

                    break; */

            }

        }



        // Ordenamos por descuento

        array_multisort(array_column($ongoing, 'discount'), SORT_DESC, $ongoing);

        array_multisort(array_column($soon, 'discount'), SORT_DESC, $soon);



        //$auctions = array_merge($ongoing, $soon, $unsold, $archived, $sold, $draft);

        //$auctions = array_merge($ongoing, $soon, $archived, $sold, $draft);

        $auctions = array_merge($ongoing, $soon, $archived, $sold);

        /* Filtramos por descuento*/

        $ventas = array();

        if($request->input('discount')){

            foreach ($auctions as $auction){

                if(($auction->discount >= 30 && $auction->discount < 90) && ($auction->auction_status_id == AuctionStatus::ONGOING || $auction->auction_status_id== AuctionStatus::SOON)){

                    array_push($ventas, $auction);

                }

            }

            $auctions = AuctionResourceWeb::collection($ventas);

        }else{

            $auctions = AuctionResourceWeb::collection($auctions);

        }



        $this->response = $auctions;

        $this->total = $auctions->count();

        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();

    }



    public function detail(Request $request, $guid)

    {

        Schema::table('actives', function (Blueprint $table) {

            if (!Schema::hasColumn('actives', 'lat')) {

                $table->string('lat')->nullable();

            }

            if (!Schema::hasColumn('actives', 'lng')) {

                $table->string('lng')->nullable();

            }

        });



        $auction = Auction::with(["technicalArchive", "landRegistryArchive", "conditionsArchive", "descriptionArchive","technicalArchiveTwo", "landRegistryArchiveTwo", "conditionsArchiveTwo", "descriptionArchiveTwo"])

            ->selectraw(

                "auctions.id,

                auctions.guid,

                auctions.title,

                Count(bids.id) as bids,

                Count(direct_sale_offers.id) as offers,

                auctions.end_date,

                auctions.start_date,

                auctions.deposit,

                auctions.start_price,

                auctions.appraisal_value,

                auctions.commission,

                auctions.minimum_bid,

                auctions.bid_price_interval,

                auctions.auction_type_id,

                auction_types.name as type,

                auction_statuses.name as status,

                actives.id as active_id,

                actives.address,

                actives.lat,

                actives.lng,

                actives.city,

                actives.refund,

                active_conditions.name as active_condition,

                provinces.name as province,

                auctions.description,

                auctions.technical_specifications,

                auctions.land_registry,

                auctions.conditions,

                auctions.description_archive_id,

                auctions.technical_archive_id,

                auctions.land_registry_archive_id,

                auctions.conditions_archive_id,

                auctions.description_archive_two_id,

                auctions.technical_archive_two_id,

                auctions.land_registry_archive_two_id,

                auctions.conditions_archive_two_id,

                auctions.video,

                auctions.video_file,

                auctions.dontshowtimer,

                auctions.link_rewrite,

                auctions.meta_description,

                auctions.meta_keywords,

                auctions.meta_title,

                auctions.link_rewrite,

                auctions.meta_title,

                auctions.meta_description,

                auctions.meta_keywords,

                auctions.auto"

            )

            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")

            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")

            ->join("actives", "auctions.active_id", "=", "actives.id")

            ->join("active_conditions", "actives.active_condition_id", "=", "active_conditions.id")

            ->join("provinces", "actives.province_id", "=", "provinces.id")

            ->leftJoin("bids", "auctions.id", "=", "bids.auction_id")

            ->leftJoin("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")

            ->groupBy("auctions.id")

            ->where("auctions.link_rewrite", $guid)

            ->first();



        if (!$auction)

        {

            $this->code = ResponseAlias::HTTP_NOT_FOUND;

            return $this->sendResponse();

        }



        $this_auction = Auction::where('link_rewrite', $guid)->first();

        $this_auction->views++;

        $this_auction->save();



        $auction->is_favorite = (bool) Favorite::where('user_id', Auth::id())

            ->where('auction_id', $this_auction->id)

            ->first();





        $membresias = Membresia::where('auction_id', $this_auction->id)->count();

        if($membresias==0){

            $auction->membresia_forbidden = false;

        }else{

            $membresia_user = Membresia::where('auction_id', $this_auction->id)->where('user_id', Auth::id()) ->count();

            $auction->membresia_user = $membresia_user;

            if($membresia_user==0){

                $auction->membresia_forbidden = true;

            }else{

                $auction->membresia_forbidden = false;

            }

        }





        $deposit = Deposit::where('user_id', Auth::id())

            ->where('auction_id', $this_auction->id)

            ->orderBy('id', 'desc')

            ->first();



        $auction->deposit_valid = $auction->deposit == 0

            || ($deposit && $deposit->status == 1);



        $auction->deposit_status_id = $deposit ? $deposit->status : null;



        if (Auth::id()) {

            if (Auth::user()->role_id == 5) {

                $auction->can_bid = true;

                $auction->deposit_valid = true;

            }else{

                $auction->can_bid = ($auction->deposit == 0 || ($deposit && $deposit->status == 1))

                    && (bool)Auth::user() && (bool)Auth::user()->confirmed && Auth::user()->status == 1;

            }

        }else{

            $auction->can_bid = ($auction->deposit == 0 || ($deposit && $deposit->status == 1))

                && (bool)Auth::user() && (bool)Auth::user()->confirmed && Auth::user()->status == 1;

        }



        /*if((bool)Auth::user()){

            if(Auth::id()==27 || Auth::user()->role_id == 5){

                $auction->acredor_privilegiado = "autenticado y acreedor";

            }else{

                $auction->acredor_privilegiado = "solo autenticado";

            }



        }else{

            $auction->acredor_privilegiado = "No autenticado";

        }*/



        if((bool)Auth::user() && (Auth::id()==27 || Auth::user()->role_id == 5)){

            $auction->acredor_privilegiado = true;

            $auction->can_bid = true;

        }else{

            $auction->acredor_privilegiado = false;

        }



        if ($auction->auction_type_id == 1)

        {

            // Bids (for direct sale)



            $lastBid = Bid::with(["user"])

                ->where("bids.auction_id", $this_auction->id)

                ->orderBy("bids.id", "desc")

                ->first();



            $myLastBid = Bid::select("bids.import", "bids.user_id")

                ->where("bids.auction_id", $this_auction->id)

                ->where("user_id", Auth::id())

                ->orderBy("bids.id", "desc")

                ->first();



            $iAmLastBidder = $lastBid && $myLastBid && $lastBid->user_id == $myLastBid->user_id;



            $auction->i_am_last_bidder = $iAmLastBidder;

            $auction->my_last_bid = $myLastBid ? $myLastBid->import : null;



            $auction->max_bidder = $lastBid ? substr($lastBid->user->firstname, 0, 3).'*' : null;

            $auction->max_bid = $lastBid ? $lastBid->import : null;

        }

        else if ($auction->auction_type_id == 2 || $auction->auction_type_id == 3)

        {

            // Offers (for direct sale)



            $lastOffer = DirectSaleOffer::with(["user"])

                ->where("direct_sale_offers.auction_id", $this_auction->id)

                ->orderBy("direct_sale_offers.import", "desc")

                ->first();



            $myLastOffer = DirectSaleOffer::select("direct_sale_offers.import", "direct_sale_offers.user_id")

                ->where("direct_sale_offers.auction_id", $this_auction->id)

                ->where("user_id", Auth::id())

                ->orderBy("direct_sale_offers.import", "desc")

                ->first();



            $iAmLastOffer = $lastOffer && $myLastOffer && $lastOffer->user_id == $myLastOffer->user_id;



            $auction->i_am_last_bidder = $iAmLastOffer;

            $auction->my_last_bid = $myLastOffer ? $myLastOffer->import : null;



            $auction->max_bidder = $lastOffer ? substr($lastOffer->user->firstname, 0, 3).'*' : null;

            $auction->max_bid = $lastOffer ? $lastOffer->import : null;

        }



        if($auction->status == "Próximamente"){

            $auction->seconds_to_end = Carbon::parse($auction->start_date)->diffInSeconds(Carbon::now()->toDateTimeString())*1000;

        }else{

            // FIX 2022-06-20: end_date might be past, avoid absolute values from diffInSeconds

            // $auction->seconds_to_end = Carbon::parse($auction->end_date)->diffInSeconds(Carbon::now()->toDateTimeString())*1000;

            $diff = Carbon::now()->diffInSeconds(Carbon::parse($auction->end_date), false);

            $auction->seconds_to_end = ($diff > 0) ? $diff*1000 : 0;

        }



        $auction = AuctionResourceWeb::make($auction);



        $this->response = $auction;

        $this->total = 1;

        $this->code = ResponseAlias::HTTP_OK;



        return $this->sendResponse();

    }



    /**

     * Creates a deposit for an auction.

     *

     * @param  int  $guid

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function createDeposit($guid, Request $request)

    {

        $validator = Validator::make($request->all(), [

            'file' => 'required|mimes:jpg,jpeg,png,pdf',

        ]);

        if (Auth::user()->status == 0) {
            $this->messages[] = 'El usuario debe estar validado para poder subir el comprobante';

            $this->code = 419;



            return $this->sendResponse();
        }



        if ($validator->fails())

        {

            $this->messages[] = $validator->errors()->messages();

            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;

        }

        else

        {

            $file = Storage::disk("public")->put("", $request->file("file"));

            $archive = Archive::create([

                "name" => $request->file("file")->getClientOriginalName(),

                "path" => $file

            ]);



            $auction = Auction::where('link_rewrite', $guid)->first();



            if (!$auction)

            {

                $this->code = ResponseAlias::HTTP_NOT_FOUND;

                return $this->sendResponse();

            }



            Deposit::create([

                'archive_id' => $archive->id,

                'user_id' => Auth::id(),

                'auction_id' => $auction->id,

                'deposit' => $auction->deposit,

            ]);



            $this->code = ResponseAlias::HTTP_CREATED;



            Notification::create([

                'title' => __('notifications.deposit.title', [

                    'firstname' => Auth::user()->firstname,

                    'lastname' => Auth::user()->lastname,

                ]),

                'subtitle' => __('notifications.deposit.subtitle', [

                    'title' => $auction->title,

                    'reference' => $auction->id,

                ]),

                'user_id' => Auth::id(),

                'auction_id' => $auction->id,

                'type_id' => Notification::DEPOSIT,

            ]);

        }



        return $this->sendResponse();

    }



    /**

     * Makes an auction favorite.

     *

     * @param  int  $guid

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function putFavorite($guid, Request $request)

    {

        $auction = Auction::where('link_rewrite', $guid)->first();



        if (!$auction)

        {

            $this->code = ResponseAlias::HTTP_NOT_FOUND;

            return $this->sendResponse();

        }



        $fav = Favorite::where('user_id', Auth::id())

            ->where('auction_id', $auction->id)

            ->first();



        if (!$fav)

        {

            Favorite::create([

                "user_id" => Auth::id(),

                "auction_id" => $auction->id,

                "status" => 0,

            ]);

            $this->total = 1;

        }

        else { $fav->delete(); $this->total = 0; }



        $this->code = ResponseAlias::HTTP_OK;



        return $this->sendResponse();

    }





    public function checkMembresia(Request $request, $guid){



        $this_auction = Auction::where('link_rewrite', $guid)->first();

        if (!$this_auction)

        {

            $this->code = ResponseAlias::HTTP_NOT_FOUND;

            return $this->sendResponse();

        }

        $this_auction->save();



        $membresias = Membresia::where('auction_id', $this_auction->id)->count();

        if($membresias==0){

            $this_auction->membresia_forbidden = false;

        }else{

            $membresia_user = Membresia::where('auction_id', $this_auction->id)->where('user_id', Auth::id()) ->count();

            if($membresia_user==0){

                $this_auction->membresia_forbidden = true;

            }else{

                $this_auction->membresia_forbidden = false;

            }

        }



        $this->response = $this_auction;

        $this->total = 1;

        $this->code = ResponseAlias::HTTP_OK;



        return $this->sendResponse();



    }



    public function listLast(Request $request)

    {

        Schema::table('actives', function (Blueprint $table) {

            if (!Schema::hasColumn('actives', 'lat')) {

                $table->string('lat')->nullable();

            }

            if (!Schema::hasColumn('actives', 'lng')) {

                $table->string('lng')->nullable();

            }

        });



        $auctionQuery = Auction::with(['bids', 'last_bid', 'offers', 'last_offer'])

            ->selectraw(

                "auctions.id,

                actives.active_category_id,

                auctions.guid,

                auctions.title,

                auctions.active_id,

                auctions.auction_type_id,

                auctions.start_date,

                auctions.appraisal_value,

                auctions.end_date,

                auctions.start_price,

                auctions.auction_status_id,

                auction_statuses.name as status,

                provinces.name as province,

                auction_types.name as type,

                auctions.dontshowtimer,

                auctions.link_rewrite,

                auctions.auto,

                actives.address,

                actives.lat,

                actives.lng,

                actives.city",

            )

            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")

            ->join("actives", "auctions.active_id", "=", "actives.id")

            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")

            ->join("provinces", "actives.province_id", "=", "provinces.id")

            ->where("auctions.auction_status_id","=",1)

            ->whereBetween('auctions.end_date',[Carbon::now(),Carbon::now()->addDays(2)])

            ->groupBy("auctions.id");



        $auctions = $auctionQuery->get();



        if($auctions->count()==0){



            $auctionQuery = Auction::with(['bids', 'last_bid', 'offers', 'last_offer'])

            ->selectraw(

                "auctions.id,

                actives.active_category_id,

                auctions.guid,

                auctions.title,

                auctions.active_id,

                auctions.auction_type_id,

                auctions.start_date,

                auctions.appraisal_value,

                auctions.end_date,

                auctions.start_price,

                auctions.auction_status_id,

                auction_statuses.name as status,

                provinces.name as province,

                auction_types.name as type,

                auctions.dontshowtimer,

                auctions.link_rewrite,

                auctions.auto,

                actives.address,

                actives.lat,

                actives.lng,

                actives.city",

            )

            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")

            ->join("actives", "auctions.active_id", "=", "actives.id")

            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")

            ->join("provinces", "actives.province_id", "=", "provinces.id")

            ->where("auctions.auction_status_id","=",1)

            ->whereBetween('auctions.end_date',[Carbon::now(),Carbon::now()->addDays(7)])  //11/04/2023  18/04/2023

            ->groupBy("auctions.id");



        $auctions = $auctionQuery->get();

        }





        foreach ($auctions as $i => $auction)

        {

            $auction->total_bids = count($auction->bids);

            /*$auction->total_offers = count($auction->offers);*/

            $auction->total_offers = 0;

            $all_offers = $auction->offers;

            foreach($all_offers as $offer){

                if($offer->status != 2){

                    $auction->total_offers++;

                }

            }

            //unset($auction->id);

            unset($auction->bids);

            unset($auction->offers);



            if($auction->auction_status_id == AuctionStatus::SOON){

                $auction->seconds_to_end = Carbon::parse($auction->start_date)->diffInSeconds(Carbon::now()->toDateTimeString())*1000;

            }else{

                // FIX 2022-06-20: end_date might be past, avoid absolute values from diffInSeconds

                // $auction->seconds_to_end = Carbon::parse($auction->end_date)->diffInSeconds(Carbon::now()->toDateTimeString())*1000;

                $diff = Carbon::now()->diffInSeconds(Carbon::parse($auction->end_date), false);

                $auction->seconds_to_end = ($diff > 0) ? $diff*1000 : 0;

            }

        }



        $auctions = AuctionResourceWeb::collection($auctions);



        $this->response = $auctions;

        $this->total = $auctions->count();

        $this->code = ResponseAlias::HTTP_OK;



        return $this->sendResponse();

    }



    public function listSoon(Request $request)

    {

        Schema::table('actives', function (Blueprint $table) {

            if (!Schema::hasColumn('actives', 'lat')) {

                $table->string('lat')->nullable();

            }

            if (!Schema::hasColumn('actives', 'lng')) {

                $table->string('lng')->nullable();

            }

        });



        $auctionQuery = Auction::with(['bids', 'last_bid', 'offers', 'last_offer'])

            ->selectraw(

                "auctions.id,

                actives.active_category_id,

                auctions.guid,

                auctions.title,

                auctions.active_id,

                auctions.auction_type_id,

                auctions.start_date,

                auctions.appraisal_value,

                auctions.end_date,

                auctions.start_price,

                auctions.auction_status_id,

                auction_statuses.name as status,

                provinces.name as province,

                auction_types.name as type,

                auctions.dontshowtimer,

                auctions.link_rewrite,

                auctions.auto,

                actives.address,

                actives.lat,

                actives.lng,

                actives.city",

            )

            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")

            ->join("actives", "auctions.active_id", "=", "actives.id")

            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")

            ->join("provinces", "actives.province_id", "=", "provinces.id")

            ->where("auctions.auction_status_id","=",7)

            ->groupBy("auctions.id");



        $auctions = $auctionQuery->get();



        if($auctions->count()==0){



            $auctionQuery = Auction::with(['bids', 'last_bid', 'offers', 'last_offer'])

            ->selectraw(

                "auctions.id,

                actives.active_category_id,

                auctions.guid,

                auctions.title,

                auctions.active_id,

                auctions.auction_type_id,

                auctions.start_date,

                auctions.appraisal_value,

                auctions.end_date,

                auctions.start_price,

                auctions.auction_status_id,

                auction_statuses.name as status,

                provinces.name as province,

                auction_types.name as type,

                auctions.dontshowtimer,

                auctions.link_rewrite,

                auctions.auto,

                actives.address,

                actives.lat,

                actives.lng,

                actives.city",

            )

            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")

            ->join("actives", "auctions.active_id", "=", "actives.id")

            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")

            ->join("provinces", "actives.province_id", "=", "provinces.id")

            ->where("auctions.auction_status_id","=",7)

            ->groupBy("auctions.id");



        $auctions = $auctionQuery->get();

        }





        foreach ($auctions as $i => $auction)

        {

            $auction->total_bids = count($auction->bids);

            /*$auction->total_offers = count($auction->offers);*/

            $auction->total_offers = 0;

            $all_offers = $auction->offers;

            foreach($all_offers as $offer){

                if($offer->status != 2){

                    $auction->total_offers++;

                }

            }

            //unset($auction->id);

            unset($auction->bids);

            unset($auction->offers);



            if($auction->auction_status_id == AuctionStatus::SOON){

                $auction->seconds_to_end = Carbon::parse($auction->start_date)->diffInSeconds(Carbon::now()->toDateTimeString())*1000;

            }else{

                // FIX 2022-06-20: end_date might be past, avoid absolute values from diffInSeconds

                // $auction->seconds_to_end = Carbon::parse($auction->end_date)->diffInSeconds(Carbon::now()->toDateTimeString())*1000;

                $diff = Carbon::now()->diffInSeconds(Carbon::parse($auction->end_date), false);

                $auction->seconds_to_end = ($diff > 0) ? $diff*1000 : 0;

            }

        }



        $auctions = AuctionResourceWeb::collection($auctions);



        $this->response = $auctions;

        $this->total = $auctions->count();

        $this->code = ResponseAlias::HTTP_OK;



        return $this->sendResponse();

    }



    public function listOffers(Request $request)

    {

        Schema::table('actives', function (Blueprint $table) {

            if (!Schema::hasColumn('actives', 'lat')) {

                $table->string('lat')->nullable();

            }

            if (!Schema::hasColumn('actives', 'lng')) {

                $table->string('lng')->nullable();

            }

        });



        $auctionQuery = Auction::with(['bids', 'last_bid', 'offers', 'last_offer'])

            ->selectraw(

                "auctions.id,

                actives.active_category_id,

                auctions.guid,

                auctions.title,

                auctions.active_id,

                auctions.auction_type_id,

                auctions.start_date,

                auctions.appraisal_value,

                auctions.end_date,

                auctions.start_price,

                auctions.auction_status_id,

                auction_statuses.name as status,

                provinces.name as province,

                auction_types.name as type,

                auctions.dontshowtimer,

                auctions.link_rewrite,

                auctions.auto,

                actives.address,

                actives.lat,

                actives.lng,

                actives.city",

            )

            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")

            ->join("actives", "auctions.active_id", "=", "actives.id")

            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")

            ->join("provinces", "actives.province_id", "=", "provinces.id")

            ->where("auctions.auction_status_id","=",1)

            ->whereBetween('auctions.end_date',[Carbon::now(),Carbon::now()->startOfMonth()->add(1,'month')])

            ->groupBy("auctions.id");



        $auctionQuery->where(function ($query) use ($request) {

            $query->whereIn('provinces.name', ['Madrid','Barcelona','Málaga']);

        });



        $auctions = $auctionQuery->get();



        foreach ($auctions as $i => $auction)

        {

            $auction->total_bids = count($auction->bids);

            /*$auction->total_offers = count($auction->offers);*/

            $auction->total_offers = 0;

            $all_offers = $auction->offers;

            foreach($all_offers as $offer){

                if($offer->status != 2){

                    $auction->total_offers++;

                }

            }

            //unset($auction->id);

            unset($auction->bids);

            unset($auction->offers);



            if($auction->auction_status_id == AuctionStatus::SOON){

                $auction->seconds_to_end = Carbon::parse($auction->start_date)->diffInSeconds(Carbon::now()->toDateTimeString())*1000;

            }else{

                // FIX 2022-06-20: end_date might be past, avoid absolute values from diffInSeconds

                // $auction->seconds_to_end = Carbon::parse($auction->end_date)->diffInSeconds(Carbon::now()->toDateTimeString())*1000;

                $diff = Carbon::now()->diffInSeconds(Carbon::parse($auction->end_date), false);

                $auction->seconds_to_end = ($diff > 0) ? $diff*1000 : 0;

            }

        }



        $auctions = AuctionResourceWeb::collection($auctions);



        $this->response = $auctions;

        $this->total = $auctions->count();

        $this->code = ResponseAlias::HTTP_OK;



        return $this->sendResponse();

    }



    public function listFinished(Request $request)

    {

        $auctionQuery = Auction::with(['bids', 'last_bid', 'offers', 'last_offer'])

            ->selectraw(

                "auctions.id,

                auctions.guid,

                auctions.title,

                Max(bids.import) as max_bid,

                Max(direct_sale_offers.import) as max_offer,

                auctions.active_id,

                auctions.auction_type_id,

                auctions.start_date,

                auctions.appraisal_value,

                auctions.end_date,

                auctions.start_price,

                auctions.auction_status_id,

                auction_statuses.name as status,

                provinces.name as province,

                auction_types.name as type,

                auctions.dontshowtimer,

                auctions.link_rewrite,

                auctions.auto,

                actives.address,

                actives.city",

            )

            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")

            ->join("actives", "auctions.active_id", "=", "actives.id")

            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")

            ->join("provinces", "actives.province_id", "=", "provinces.id")

            ->wherein('auctions.auction_status_id',[3,5,6])

            //->take(30)

            ->groupBy("auctions.id");



            $auctionQuery->when($request->input('interacted', false), function (Builder $builder) use ($request) {



                $builder->leftJoin("bids", "auctions.id", "=", "bids.auction_id")

                    ->leftJoin("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")

                    ->havingRaw('Count(bids.id) > 0 OR Count(direct_sale_offers.id) > 0')

                    ->where('bids.user_id', Auth::id())

                    ->orWhere('direct_sale_offers.user_id', Auth::id());





            }, function (Builder $builder) use ($request) {



                $builder->when($request->input('bidded', false), function (Builder $builder) use ($request) {

                    $builder->join("bids", "auctions.id", "=", "bids.auction_id")

                        ->where('bids.user_id', Auth::id());



                }, function (Builder $builder) use ($request) {

                    $builder->leftJoin("bids", "auctions.id", "=", "bids.auction_id");



                });



                $builder->when($request->input('offered', false), function (Builder $builder) use ($request) {

                    $builder->join("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")

                        ->where('direct_sale_offers.user_id', Auth::id());



                }, function (Builder $builder) use ($request) {

                    $builder->leftJoin("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id");



                });

            });



        $auctions = $auctionQuery->get();



        foreach ($auctions as $i => $auction)

        {

            $auction->total_bids = count($auction->bids);



            $auction->total_offers = 0;

            $all_offers = $auction->offers;

            foreach($all_offers as $offer){

                if($offer->status != 2){

                    $auction->total_offers++;

                }

            }

            //unset($auction->id);

            unset($auction->bids);

            unset($auction->offers);



            if($auction->auction_status_id == AuctionStatus::SOON){

                $auction->seconds_to_end = Carbon::parse($auction->start_date)->diffInSeconds(Carbon::now()->toDateTimeString())*1000;

            }else{

                // FIX 2022-06-20: end_date might be past, avoid absolute values from diffInSeconds

                // $auction->seconds_to_end = Carbon::parse($auction->end_date)->diffInSeconds(Carbon::now()->toDateTimeString())*1000;

                $diff = Carbon::now()->diffInSeconds(Carbon::parse($auction->end_date), false);

                $auction->seconds_to_end = ($diff > 0) ? $diff*1000 : 0;

            }

        }

        list($ventas, $i, $subastas) = array(array(),0,0);

        foreach ($auctions as $auction){



            if($auction->total_bids > 2 && $subastas <3){

                array_push($ventas, $auction);

                $subastas++;

            }

        }



        foreach ($auctions as $auction){

            if($auction->total_offers > 0 && $i<=9){

                array_push($ventas, $auction);

                $i++;

            }

        }



        shuffle($ventas);



        $auctions = AuctionResourceWeb::collection($ventas);



        $this->response = $auctions;

        $this->total = $auctions->count();

        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();

    }



    public function testEmail(){

        //var_dump("testing mail");





        /* $username = 'tu_correo@dominio.com';

        $password = 'tu_contraseña'; */



/*         $hostname = '{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX';

        $username = 'info.com';

        $password = '0çRK'; */



        $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';

        $username = 'his@gmail.com';

        $password = '@';



        // Intentamos conectarnos al servidor de correo

        $inbox = imap_open($hostname,$username,$password) or die('No se pudo conectar: ' . imap_last_error());





        // Obtenemos los correos

        $emails = imap_search($inbox,'ALL');



        if($emails) {

            // Si hay correos, los ordenamos de más reciente a más antiguo

            rsort($emails);



            // Recorremos cada correo

            foreach($emails as $email_number) {

                // Obtenemos los detalles del correo

                $overview = imap_fetch_overview($inbox,$email_number,0);

                $message = imap_fetchbody($inbox,$email_number,2);



                // Imprimimos los detalles del correo

                echo "=====================================\n";

                echo "Asunto: " . $overview[0]->subject . "\n";

                echo "De: " . $overview[0]->from . "\n";

                echo "Fecha: " . $overview[0]->date . "\n";

                echo "Mensaje:\n" . $message;

                echo "\n=====================================\n";

            }

        }



        // Cerramos la conexión

        imap_close($inbox);

    }



    public function saveLatLng(Request $request)

    {

        $ac = Active::find($request->active_id);

        $ac->lat = $request->lat;

        $ac->lng = $request->lng;

        $ac->save();

    }



}

