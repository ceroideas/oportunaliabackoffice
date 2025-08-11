<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiController;
use App\Http\Resources\AuctionResourceWeb;
use App\Http\Resources\UserResource;
use App\Mail\Contact;
use App\Models\Archive;
use App\Models\Auction;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Participant;
use App\Repositories\UserRepositoryInterface;
use App\Rules\Dni;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use JWTAuth;
use Mail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Exceptions\JWTException;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


use Illuminate\Support\Facades\Http;

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
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function confirm(Request $request, $token)
    {
        $user = User::where('activation_token', $token)
            ->whereNull('deleted_at')
            ->first();

        if (!$user){
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }else{
            $user->confirmed = true;
            $user->activation_token = null;
            $user->save();
            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }

    public function interests(Request $request)
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'interests')) {
                $table->text('interests')->nullable();
            }
        });

        $user = User::with(["document"])
            ->select("users.*")
            ->where("users.id", Auth::id())
            ->first();

        $user->interests = $request->all();
        $user->save();

        return $user;
    }

    public function profile(Request $request)
    {
        $user = User::with(["document"])
            ->select("users.*")
            ->where("users.id", Auth::id())
            ->first();

        if (!$user)
        {
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }

        $user = UserResource::make($user);

        $this->response = [
            'user' => $user,
            'my_auctions' => [],
            'favorites' => [],
            'representation' => []
        ];
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    public function detail(Request $request)
    {
        $user = User::with(["document","documentTwo"])->
            select(
                "users.username",
                "users.firstname",
                "users.lastname",
                "users.birthdate",
                "users.email",
                "users.phone",
                "users.address",
                "users.city",
                "users.cp",
                "users.country_id",
                "users.province_id",
                "users.document_number",
                "users.confirmed",
                "users.created_at",
                "users.notification_news",
                "users.notification_auctions",
                "users.notification_favorites",
                "users.status",
                "users.lang",
                "users.archive_id",
                "users.archive_two_id",
                "users.interests",
            )
            ->where("users.id", Auth::id())
            ->first();

        if (!$user)
        {
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }

        $user = UserResource::make($user);

        $this->response = $user;
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    public function setPreferences(Request $request){


        $input = $request->toArray();
        if(count($input)){
            $serial = json_encode($input);

            $preferences = UserNotification::where('user_id', Auth::id())
                            ->get();  // collection entonces es array y debe iterar

            if($preferences->count()!=0){
                foreach($preferences as $value){
                    $value->notification_news = $input['pref1'];
                    $value->notifications = $serial;
                    $value->save();
                }
                $this->response = 'Preferencias actualizadas correctamente';
                $this->code = ResponseAlias::HTTP_OK;
            }else{
                UserNotification::create([
                    'user_id' => Auth::id(),
                    'notification_news'=>$input['pref1'],
                    'notifications'=>$serial
                ]);
                $this->response = 'Preferencias almacenadas correctamente';
                $this->code = ResponseAlias::HTTP_OK;
            }
        }else{
            $this->response = 'No hay elementos';
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }
        return $this->sendResponse();
    }

    public function testSetPreferences(Request $request){
        echo "testSetPrefereces";
        $input = $request->toArray();

        if(count($input)){
            $serial = json_encode($input);

            UserNotification::create([
                'user_id' => 27,
                'notification_news'=>1,//$input['notification_news'],
                'notifications'=>$serial
            ]);


        }else{
            $this->response = 'No hay elementos';
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }

        $this->response = 'Preferencias almacenadas correctamente';
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();

    }

    /**
     * Stores data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function testCreatePreferences(Request $request)
    {

        dd($request);
        $input = $request->toArray();

        if(count($input)){
            $serial = json_encode($input);

            UserNotification::create([
                'user_id' => 27,
                'notification_news'=>1,//$input['notification_news'],
                'notifications'=>$serial
            ]);
        }

        die();
        $input = $request->toArray();
        //$input = json_encode($request->all());   //{"headers":{"normalizedNames":[],"lazyUpdate":null}}

        UserNotification::create([
            'user_id' => 27,
            'notification_news'=>1,//$input['notification_news'],
            'notifications'=>$input
        ]);


        $validator = Validator::make($request->all(), [
            'notifications' => ''
        ]);

        UserNotification::create([
            'user_id' => 27,
            'notification_news'=>1,//$input['notification_news'],
            'notifications'=>$validator
        ]);

        if ($validator->fails()){
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }else{
            /*$file = Storage::disk("public")->put("", $request->file("file"));
            $archive= Archive::create([
                "name" => $request->file("file")->getClientOriginalName(),
                "path" => $file
            ]);*/



            /*$representation = Representation::create(array_merge(
                Arr::except($validator->validated(), 'file'),
                [
                    'guid' => (string) Str::uuid(),
                    'archive_id' => $archive->id,
                    'user_id' => Auth::id(),
                ]
            ));*/

            UserNotification::create([
                'user_id' => 27,
                'notification_news'=>1,//$input['notification_news'],
                'notifications'=>$validator
            ]);

            $this->code = ResponseAlias::HTTP_CREATED;

            /*Notification::create([
                'title' => __('notifications.representation.title', [
                    'firstname' => Auth::user()->firstname,
                    'lastname' => Auth::user()->lastname,
                ]),
                'subtitle' => __('notifications.representation.subtitle', [
                    'alias' => $representation->alias,
                ]),
                'user_id' => Auth::id(),
                'representation_id' => $representation->id,
                'type_id' => Notification::REPRESENTATION,
            ]);*/
        }

        return $this->sendResponse();
    }


    /*public function testPreferences(){
        echo"Test Preferences route";
    }*/

    public function getPreferences(Request $request)
    {
        echo "getPreferences";
        $userNotification = UserNotification::where("user_id",27)
                                ->get();

        if (!$userNotification)
        {
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }

        $elements = json_decode($userNotification[0]->notifications, true);
        $elements["notification_news"]= $userNotification[0]->notification_news;
        $elements["user_id"]= $userNotification[0]->user_id;

        //dd(json_encode($elements));

        //$userNotification = UserResource::make($userNotification);
        $this->response = $elements;
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;


        return $this->sendResponse();
    }


    public function edit(Request $request)
    {
        $user = User::find(Auth::id());

        $rules = [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'phone' => 'required|digits:9',
            'birthdate' => ['required', 'before:18 years ago', 'date_format:Y-m-d'],
            'address' => '',
            'city' => '',
            'province_id' => '',
            'country_id' => '',
            'cp' => ''
        ];

        if ($user->status == 2 || $user->status == 1)
        {
            $rules['document_number'] = ['required', new Dni()];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $values = array_merge($validator->validated());
            $user->update($values);
            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }

    public function changePassword(Request $request)
    {
        $user = User::find(Auth::id());

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('validation.not_match');
                }
            }],
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::min(6), 'confirmed'],
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $user->update(['password' => bcrypt($request->password)]);
            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }

    public function uploadDni(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|mimes:jpg,jpeg,png,pdf',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            if ($request->hasFile("document"))
            {
                $file = Storage::disk("public")->put("", $request->file("document"));
                $user = Auth::user();
                $lastDocument = $user->archive_id;
                $archive = Archive::create([
                    "name" => $request->file("document")->getClientOriginalName(),
                    "path" => $file
                ]);

                $user->update([
                    'archive_id' => $archive->id,
                    'status' => 0,
                ]);

                if ($lastDocument) {
                    $archiveDelete = Archive::find($lastDocument);
                    Storage::disk("public")->delete($archiveDelete->path);
                }

                Notification::create([
                    'title' => __('notifications.document.title', [
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                    ]),
                    'subtitle' => __('notifications.document.subtitle', [
                        'document_number' => $user->document_number,
                    ]),
                    'user_id' => Auth::id(),
                    'type_id' => Notification::DOCUMENT,
                ]);
            }

            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }

    public function uploadDniTwo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|mimes:jpg,jpeg,png,pdf',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            if ($request->hasFile("document"))
            {
                $file = Storage::disk("public")->put("", $request->file("document"));
                $user = Auth::user();
                $lastDocument = $user->archive_two_id;
                $archive = Archive::create([
                    "name" => $request->file("document")->getClientOriginalName(),
                    "path" => $file
                ]);

                $user->update([
                    'archive_two_id' => $archive->id,
                    'status' => 0,
                ]);

                if ($lastDocument) {
                    $archiveDelete = Archive::find($lastDocument);
                    Storage::disk("public")->delete($archiveDelete->path);
                }

                Notification::create([
                    'title' => __('notifications.document.title', [
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                    ]),
                    'subtitle' => __('notifications.document.subtitle', [
                        'document_number' => $user->document_number,
                    ]),
                    'user_id' => Auth::id(),
                    'type_id' => Notification::DOCUMENT,
                ]);
            }

            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }

    public function updateNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_news' => 'required|boolean',
            'notification_auctions' => 'required|boolean',
            'notification_favorites' => 'required|boolean',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            Auth::user()->update($validator->validated());
            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }

    public function updatePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_news' => 'required|boolean',
            'notification_auctions' => 'required|boolean',
            'notification_favorites' => 'required|boolean',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            Auth::user()->update($validator->validated());
            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }

    public function deletePermanent(Request $request)
    {
        $this->userRepository->delete(Auth::id());
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();
    }

    public function auctions(Request $request)
    {
        $this->code = ResponseAlias::HTTP_OK;
        $auctionQuery = Auction::selectraw("auctions.id,auctions.title,Count(bids.id) as bids,Max(bids.import) as max_bid,
        auctions.start_date,auctions.end_date,auctions.deposit,
        auctions.start_price, auctions.appraisal_value, auctions.created_at,
        auction_statuses.name as status, provinces.name as province, actives.id as active_id,
        auction_types.name as type")
            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
            ->join("actives", "auctions.active_id", "=", "actives.id")
            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")
            ->join("provinces", "actives.province_id", "=", "provinces.id")
            ->join("bids", "auctions.id", "=", "bids.auction_id")
            ->where("bids.user_id","=",Auth::id())
            ->groupBy("auctions.id");
        $auctionQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('province', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('title', 'LIKE', '%' . $request->input('search') . '%');
        });
        $auctionQuery->when($request->has('auction_status_id'), function (Builder $builder) use ($request) {
            $builder->where('auctions.auction_status_id', '=', $request->input('auction_status_id'));
        });
        $auctionQuery->when($request->has('auction_type_id'), function (Builder $builder) use ($request) {
            $builder->where('auctions.auction_type_id', '=', $request->input('auction_status_id'));
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

        $this->response = AuctionResourceWeb::collection($auctions);
        $this->total = $auctions->count();
        return $this->sendResponse();
    }

    public function favorites(Request $request)
    {
        $this->code = ResponseAlias::HTTP_OK;
        $auctionQuery = Auction::selectraw("auctions.id,auctions.title,Count(bids.id) as bids,Max(bids.import) as max_bid,
        auctions.start_date,auctions.end_date,auctions.deposit, auctions.auction_type_id, auctions.link_rewrite,
        auctions.start_price, auctions.appraisal_value, auctions.created_at,
        auction_statuses.name as status, provinces.name as province, actives.id as active_id, favorites.id as fav_id,
        auction_types.name as type")
            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
            ->join("actives", "auctions.active_id", "=", "actives.id")
            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")
            ->join("provinces", "actives.province_id", "=", "provinces.id")
            ->leftJoin("bids", "auctions.id", "=", "bids.auction_id")
            ->join("favorites", "auctions.id", "=", "favorites.auction_id")
            ->where("favorites.user_id","=",Auth::id())
            ->groupBy("auctions.id");
        $auctionQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('province', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('title', 'LIKE', '%' . $request->input('search') . '%');
        });
        $auctionQuery->when($request->has('auction_status_id'), function (Builder $builder) use ($request) {
            $builder->where('auctions.auction_status_id', '=', $request->input('auction_status_id'));
        });
        $auctionQuery->when($request->has('auction_type_id'), function (Builder $builder) use ($request) {
            $builder->where('auctions.auction_type_id', '=', $request->input('auction_type_id'));
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

        $this->response = AuctionResourceWeb::collection($auctions);
        $this->total = $auctions->count();
        return $this->sendResponse();
    }

    public function contact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|max:100',
            //'code' => 'required|string',
            /*'phone' => 'required|digits:9',
             'subject' => 'required',
            'message' => 'required', */
        ]);


        if ($validator->fails()){
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;

        }else{
            $participant = new Participant();
            $participant->campaign_id = 8;
            $participant->firstname = $request->firstname;
            $participant->lastname = $request->lastname;
            $participant->email = $request->email;
            $participant->code = 'OPTCOM';
            $participant->available=1;
            $participant->save();


            $this->sendContact($request->all());

            /* PIPEDRIVE: Create person */
            if(file_exists('testing/pipedrive.txt')){
                $file = fopen('testing/pipedrive.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'Formulario de contacto se envia a pipedrive '.PHP_EOL);
                fclose($file);
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post(config('pipedrive.pipeurl').'persons?api_token='.config('pipedrive.pitk'), [
                'name'=> $request->firstname .' '. $request->lastname,
                'owner_id'=> config('pipedrive.owner_id'),
                'org_id'=> '',
                'email'=> [
                    'value'=> $request->email,
                    'primary'=> 'true',
                    'label'=> 'work'
                ],
                'phone'=>[
                    'value'=> $request->code,
                    'primary'=> 'true',
                    'label'=> 'work'
                ],
                'label'=> config('pipedrive.cold'),
                'visible_to'=> '3' // 3 = entire company
            ])->throw()->json();

            if(file_exists('testing/pipedrive.txt')){
                $file = fopen('testing/pipedrive.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'Usuario enviado a Pipedrive (FORMULARIO DE CONTACTO) '.PHP_EOL);
                fwrite($file, date("d/m/Y H:i:s").'-'.'Response web: '. gettype($response) .PHP_EOL);
                fwrite($file, date("d/m/Y H:i:s").'-'.'Response web: '. $response['data']['id'].PHP_EOL);
                fwrite($file, date("d/m/Y H:i:s").'-'.'Success web: '. $response['success'] .PHP_EOL);
                fclose($file);
            }

            /* PIPEDRIVE: Fin de llamada para crear personas */

            /* PIPEDRIVE: Creamos lead (Deal) */
            $responseDeal = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post(config('pipedrive.pipeurl').'deals?api_token='.config('pipedrive.pitk'), [
                'title'=> 'Formulario contacto '. $request->firstname .' '. $request->lastname,
                'value'=> '',
                'user_id'=>config('pipedrive.owner_id'),
                'person_id'=>$response['data']['id'],
                'org_id'=>'',
                'pipeline_id'=>'',
                'stage_id'=>config('pipedrive.bruto'),
                'status'=>'open',
                'add_time'=>now(),
                'probability'=>50,
                'visible_to'=>'3',
            ])->throw()->json();

            if(file_exists('testing/pipedrive.txt')){
                $file = fopen('testing/pipedrive.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'Deal Bruto enviado a Dipedrive (FORMULARIO DE CONTACTO)'.PHP_EOL);
                fwrite($file, date("d/m/Y H:i:s").'-'.'Success web: '. $responseDeal['success'] .PHP_EOL);
                fwrite($file, date("d/m/Y H:i:s").'-'.'Response web: '. $responseDeal['data']['id'].PHP_EOL);
                fclose($file);
            }
            /* PIPEDRIVE: Fin creacion lead (Deal) */

            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }

    public function registerCampaign()
    {

        return view('register-campaign');

    }
    public function storeParticipant(Request $request){


        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required|string',
            //'code' => 'required|string',
        ]);
        if ($validator->fails()) {

            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            $this->messages[] = $validator->errors()->messages();

        }else{

            $participant = new Participant();
            $participant->campaign_id = 7;
            $participant->name = $request->name;
            $participant->surname = $request->surname;
            $participant->email = $request->email;
            $participant->code = 'OPTSIMA24';
            $participant->available=1;
            $participant->save();
            $this->code = ResponseAlias::HTTP_OK;
        }


        //return $this->sendResponse();
        return Redirect::to('https://oportunalia.com');
    }

    private function sendContact($data)
    {
        ($data);
        Mail::to('info@oportunalia.com')
        // Mail::to('jorgesolano92@gmail.com')
            ->send(new Contact($data));
    }
}
