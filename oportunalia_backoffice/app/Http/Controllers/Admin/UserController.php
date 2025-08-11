<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\ApiController;
use App\Http\Resources\DepositResource;
use App\Http\Resources\RepresentationResource;
use App\Http\Resources\UserResource;
use App\Models\Archive;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Deposit;
use App\Models\DirectSaleOffer;
use App\Models\Representation;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Rules\Dni;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use JWTAuth;
use Mail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class UserController extends ApiController
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Activates an user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function confirm(Request $request, $id)
    {
        $user = User::find($id);
        $user->confirmed = boolval($request->get("confirmed"));
        $user->save();
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();
    }

    /**
     * Validation of a representation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function validate(Request $request, $id)
    {

/*         if(file_exists('testing/pipedrive.txt')){
            $file = fopen('testing/pipedrive.log', 'a');
            fwrite($file, date("d/m/Y H:i:s").'-'.'User Validator' . PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.'Id: '. $id .PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.'Request: '. $request->status . PHP_EOL);
            fclose($file);
        } */


        $validator = Validator::make($request->all(), [
            'status' => 'required|integer|between:1,3',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $user = User::find($id);
            $user->status = $request->status;
            $user->save();

            if(isset($user->wp_id)){
                $this->pipeUpdate($id, $request->status);
            }


            $this->code = ResponseAlias::HTTP_OK;
        }

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
            'username' => 'required|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::min(6), 'confirmed'],
            'phone' => 'required|digits:9',
            'role_id' => 'required',

            /*'document' => 'nullable|mimes:jpg,jpeg,png,pdf',
            'document_number' => ['required', 'unique:users', new Dni()],
            'birthdate' => ['required', 'before:18 years ago', 'date_format:Y-m-d'],*/

            /* 'address' => '',
            'city' => '',
            'province_id' => '',
            'country_id' => '',
            'cp' => '', */
        ]);


        if ($validator->fails())
        {
            if(file_exists('testing/pipedrive.txt')){
                $file = fopen('testing/pipedrive.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'validator '.PHP_EOL);
                fwrite($file, date("d/m/Y H:i:s").'-'.$validator->fails().PHP_EOL);
                fclose($file);
            }

            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            $this->messages[] = $validator->errors()->messages();
        }else{
            if ($request->hasFile("document"))
            {
                $file = Storage::disk("public")->put("", $request->file("document"));
                $archive = Archive::create([
                    "name" => $request->file("document")->getClientOriginalName(),
                    "path" => $file
                ]);
            }

            $user = User::create(array_merge(
                Arr::except($validator->validated(), 'document'),
                [
                    'password' => bcrypt($request->password),
                    'archive_id' => $request->hasFile("document") && $file != null ? $archive->id : '',
                ]
            ));
            $this->code = ResponseAlias::HTTP_CREATED;
        }

        /* PIPEDRIVE: Crear Personas */
        if(file_exists('testing/pipedrive.txt')){
            $file = fopen('testing/pipedrive.log', 'a');
            fwrite($file, date("d/m/Y H:i:s").'-'.'Usuario creado y envia a pipedrive '.PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.'validator '.PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.$validator->fails().PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.gettype($user). PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.$user->firstname. PHP_EOL);
            fclose($file);
        }

        //$client = new Client();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post(config('pipedrive.pipeurl').'persons?api_token='.config('pipedrive.pitk'), [
            'name'=> $user->firstname .' '. $user->lastname,
            'owner_id'=> config('pipedrive.owner_id'),
            'org_id'=> '',
            'email'=> [
                'value'=> $user->email,
                'primary'=> 'true',
                'label'=> 'work'
            ],
            'phone'=>[
                'value'=> $user->phone,
                'primary'=> 'true',
                'label'=> 'work'
            ],
            'label'=> config('pipedrive.warm'),
            'visible_to'=> '3' // 3 entire company
        ])->throw()->json();

        if(file_exists('testing/pipedrive.txt')){
            $file = fopen('testing/pipedrive.log', 'a');
            fwrite($file, date("d/m/Y H:i:s").'-'.'Usuario enviado a pipedrive '.PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.'Response: '. gettype($response) .PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.'Response: '. $response['data']['id'].PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.'Success: '. $response['success'] .PHP_EOL);
            fclose($file);
        }
        if(isset($response['success']) && $response['success']==1){
            if(file_exists('testing/pipedrive.txt')){
                $file = fopen('testing/pipedrive.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'Update user wp_id'.PHP_EOL);
                fclose($file);
            }
            $user->wp_id = $response['data']['id'];
            $user->save();
        }

        /* PIPEDRIVE: Fin de llamada para crear personas */

        /* PIPEDRIVE: Creamos lead (Deal) */
        $responseDeal = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post(config('pipedrive.pipeurl').'deals?api_token='.config('pipedrive.pitk'), [
            'title'=> 'Usuario registrado '.$user->firstname .' '. $user->lastname,
            'value'=> '',
            'user_id'=>config('pipedrive.owner_id'),
            'person_id'=>$response['data']['id'],
            'org_id'=>'',
            'pipeline_id'=>'',
            'stage_id'=>config('pipedrive.cualificado'),
            'status'=>'open',
            'add_time'=>now(),
            'probability'=>50,
            'visible_to'=>'3',
        ])->throw()->json();

        if(file_exists('testing/pipedrive.txt')){
            $file = fopen('testing/pipedrive.log', 'a');
            fwrite($file, date("d/m/Y H:i:s").'-'.'Deal enviado a pipedrive '.PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.'Success: '. $responseDeal['success'] .PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.'Response: '. $responseDeal['data']['id'].PHP_EOL);
            fclose($file);
        }

        /* PIPEDRIVE: Fin creacion lead (Deal) */

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
        $usersQuery = User::with(['document','documentTwo'])
            ->select("users.*", "roles.description as role")
            ->join("roles", "users.role_id", "=", "roles.id");

        $usersQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('username', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('firstname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('lastname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('email', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('phone', 'LIKE', '%' . $request->input('search') . '%');
        });

        $usersQuery->when($request->has('confirmed'), function (Builder $builder) use ($request) {
            $builder->where('confirmed', '=', $request->input('confirmed'));
        });

        $usersQuery->when($request->has('role_id'), function (Builder $builder) use ($request) {
            $builder->where('roles.id', '=', $request->input('role_id'));
        });

        $usersQuery->when($request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $users = $usersQuery->get();

        $users = UserResource::collection($users);

        $this->response = $users;
        $this->total = $users->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    public function detail(Request $request, $id)
    {
        $user = User::with("document","documentTwo")
            ->select("users.*", "roles.description as role")
            ->join("roles", "users.role_id", "=", "roles.id")
            ->where("users.id", "=", $id)
            ->first();

        $bids = Bid::where("user_id","=",$id)->count();
        $user->number_bids = $bids;

        $user->password = null;

        $user = UserResource::make($user);

        $this->response = $user;
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    public function edit(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|max:100',
            'password' => ['string', 'nullable', \Illuminate\Validation\Rules\Password::min(6), 'confirmed'],
            'document_number' => ['required', new Dni()],
            'phone' => 'required|digits:9',
            'birthdate' => ['required', 'before:18 years ago', 'date_format:Y-m-d'],
            'role_id' => 'required',
            'confirmed' => 'required',
            'address' => '',
            'city' => '',
            'province_id' => '',
            'country_id' => '',
            'cp' => '',
        ]);


        if ($validator->fails()){
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;

        }else{

            if ($request->hasFile("document")) {

                $file = Storage::disk("public")->put("", $request->file("document"));
                $archive = Archive::create(["name"=>$request->file("document")->getClientOriginalName(),"path"=>$file]);
            }

            $values = array_merge(
                $validator->validated(),
                $request->hasFile("document") ? ['archive_id' => $archive->id] : [],
            );

            if ($request->hasFile("document_two")) {

                $file_two = Storage::disk("public")->put("", $request->file("document_two"));
                $archive_two = Archive::create(["name"=>$request->file("document_two")->getClientOriginalName(),"path"=>$file_two]);
            }

            // Ignoraba insertar el nuevo $values
            /*$values = array_merge(
                $validator->validated(),
                $request->hasFile("document_two") ? ['archive_two_id' => $archive_two->id] : [],
            );*/
            $values = array_merge($values, $request->hasFile("document_two") ? ['archive_two_id' => $archive_two->id] : []);

            if ($request->has("password") && $request->get("password")) {
                $values['password'] = bcrypt($request->password);
            } else {
                unset($values['password']);
            }

            User::where("id", $id)->update(Arr::except($values, 'document'));

            $this->code = ResponseAlias::HTTP_OK;

            if(file_exists('testing/pipedrive.txt')){
                $file = fopen('testing/pipedrive.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'User Edit llama pipeUpdate: ' .$request->status. 'and id: '. $id . PHP_EOL);
                fclose($file);
            }

            // Llamar a funcion que actualice a pipedrive los elementos
            $this->pipeUpdate($id, $request->status);

        }

        return $this->sendResponse();
    }

    public function deletePermanentAdmin(Request $request, $id)
    {
        $this->userRepository->delete($id);
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();

    }

    public function deleteDocumentOne(Request $request, $id)
    {
        User::where("id", $id)->update(array('archive_id' => NULL));
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();
    }

    public function deleteDocumentTwo(Request $request, $id)
    {
        User::where("id", $id)->update(array('archive_two_id' => NULL));
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();
    }

    /**
     * Returns a list of representations.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function representations($id, Request $request)
    {
        $representationsQuery = Representation::with("image")
            ->select(
                "representations.*",
                "representation_types.name as representation_type",
                "countries.name as country", "provinces.name as province"
            )
            ->join("representation_types", "representation_types.id", "=", "representation_type_id")
            ->join("countries", "countries.id", "=", "country_id")
            ->join("provinces", "provinces.id", "=", "province_id")
            ->where("user_id", "=", $id);

        $representationsQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('alias', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('firstname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('lastname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('address', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('city', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('cp', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('countries.name', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('provinces.name', 'LIKE', '%' . $request->input('search') . '%');
        });

        $representationsQuery->when($request->has('status'), function (Builder $builder) use ($request) {
            $builder->where('status', '=', $request->input('status'));
        });

        $representationsQuery->when($request->has('representation_type_id'), function (Builder $builder) use ($request) {
            $builder->where('representation_type_id', '=', $request->input('representation_type_id'));
        });

        $representationsQuery->when($request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $representations = $representationsQuery->get();

        $this->response = RepresentationResource::collection($representations);
        $this->total = $representations->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    public function directOffers(Request $request,$id)
    {
        $auctionQuery = Auction::selectraw(
            "Max(direct_sale_offers.import) as max_offer,
            auctions.id,Count(direct_sale_offers.id) as offers"
        )
        ->leftJoin("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")
        ->groupBy("auctions.id");

        $auctionQuery = Auction::selectraw(
            "direct_sale_offers.id,
            auctions.title,
            auctions.id as reference,
            auctions.start_date,
            auctions.end_date,
            auctions.start_price,
            auctions.appraisal_value,
            direct_sale_offers.user_id,
            direct_sale_offers.auction_id,
            direct_sale_offers.import,
            direct_sale_offers.status,
            sub_values.offers,
            sub_values.max_offer"
        )
        ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
        ->join("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")
        ->join(DB::raw("(".$auctionQuery->toSql().") sub_values"), function($join) {
            $join->on('direct_sale_offers.auction_id', '=', 'sub_values.id');
        })
        ->where("direct_sale_offers.user_id", "=", $id);

        $auctionQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('reference', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('auctions.title', 'LIKE', '%' . $request->input('search') . '%');
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
     * Returns a list of auctions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function auctions(Request $request, $id)
    {
        $auctionQuery = Auction::selectraw(
                "Max(bids.import) as max_bid,
                auctions.id,
                Count(bids.id) as bids"
            )
            ->leftJoin("bids", "auctions.id", "=", "bids.auction_id")
            ->groupBy("auctions.id");

        $auctionQuery =  Auction::selectraw(
            "auctions.title,
            auctions.id as reference,
            auctions.start_date,
            auctions.end_date,
            auctions.start_price,
            auctions.auction_status_id,
            bids.id,
            bids.user_id,
            bids.auction_id,
            bids.import,
            auctions.appraisal_value,
            sub_values.bids,
            sub_values.max_bid,
            auction_statuses.name as status"
        )
        ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
        ->join("bids", "auctions.id", "=", "bids.auction_id")
        ->join(DB::raw("(".$auctionQuery->toSql().") sub_values"), function($join) {
            $join->on('bids.auction_id', '=', 'sub_values.id');
        })
        ->where("bids.user_id", "=", $id);

        $auctionQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('reference', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('auctions.title', 'LIKE', '%' . $request->input('search') . '%');
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
     * Returns a list of deposits.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deposits($id, Request $request)
    {
        $depositsQuery = Deposit::with(["document"])->select(
            "deposits.id",
            "auctions.id as reference",
            "deposits.created_at",
            "deposits.deposit",
            "deposits.status",
            "archive_id"
        )
        ->join("auctions", "auctions.id", "=", "deposits.auction_id")
        ->where("user_id", "=", $id);

        $depositsQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('reference', 'LIKE', '%' . $request->input('search') . '%');
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
     * Export the resource to a file.
     *
     * @param  string  $type
     * @return any
     */
    public function export($type)
    {
        $filename = [
            config('app.name'),
            __('fields.backoffice.users'),
        ];

        switch ($type)
        {
            case 'xlsx':

                $filename = composeFilename($filename, 'xlsx');

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\Admin\UserExport, $filename
                );

            default:
                $this->code = ResponseAlias::HTTP_BAD_REQUEST;
                return $this->sendResponse();
        }
    }

    public function pipeUpdate($id, $status)
    {

        if(file_exists('testing/pipedrive.txt')){
            $file = fopen('testing/pipedrive.log', 'a');
            fwrite($file, date("d/m/Y H:i:s").'-'.'User PipeUpdate' . PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.'Id: '. $id .PHP_EOL);
            fwrite($file, date("d/m/Y H:i:s").'-'.'Status: '. $status . PHP_EOL);
            fclose($file);
        }

        $user = User::find($id);
        if($user->wp_id){

            // find deal_id
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
                    'stage_id'=>$status==1 ? config('pipedrive.validado') : config('pipedrive.cualificado'),
                    ])->throw()->json();

                $responsePerson = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])->put(config('pipedrive.pipeurl').'persons/'.$personId.'?api_token='.config('pipedrive.pitk'),[
                    'label'=>$status==1 ? config('pipedrive.hot') : config('pipedrive.warm'),
                    ])->throw()->json();

                if(file_exists('testing/pipedrive.txt')){
                    $file = fopen('testing/pipedrive.log', 'a');
                    fwrite($file, date("d/m/Y H:i:s").'-'.'Update Deal: '. $dealId . ' status: '. $status . PHP_EOL);
                    fwrite($file, date("d/m/Y H:i:s").'-'.'Update person: '. $personId .PHP_EOL);
                    fclose($file);
                }
            }

            if(file_exists('testing/pipedrive.txt')){
                $file = fopen('testing/pipedrive.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'PipeUpdate actualizado' . PHP_EOL);
                fclose($file);
            }

            $this->response = 'Pipe actualizado';
            $this->code = ResponseAlias::HTTP_OK;
            return $this->sendResponse();
        }else{

            if(file_exists('testing/pipedrive.txt')){
                $file = fopen('testing/pipedrive.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'PipeUpdate Cliente sin PipeDrive' . PHP_EOL);
                fclose($file);
            }

            $this->response = 'Cliente sin pipe';
            $this->code = ResponseAlias::HTTP_OK;
            return $this->sendResponse();

        }


    }

    public function exportInterests()
    {
        $filename = [
            config('app.name'),
            __('fields.backoffice.users'),
        ];

        $filename = composeFilename($filename, 'xlsx');

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Admin\UserInterestsExport, $filename
        );
    }
}
