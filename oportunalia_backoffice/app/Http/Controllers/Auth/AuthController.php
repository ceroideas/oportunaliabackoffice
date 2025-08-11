<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\ApiController;
use App\Rules\Dni;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Illuminate\Support\Facades\Storage;



use App\Models\User;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Deposit;
use App\Models\Representation;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\DirectSaleOffer;
use App\Models\Archive;

use App\Mail\RecoverPassword;
use App\Mail\Welcome;
use App\Mail\DenyBid;
use App\Mail\SuccessBid;
use App\Mail\DepositInValid;
use App\Mail\DepositValid;
use App\Mail\RepresentationInValid;
use App\Mail\RepresentationValid;
use App\Mail\AuctionEnd;
use App\Mail\FavEnd;
use App\Mail\WinBid;
use App\Mail\EndAdministrator;
use App\Mail\FavStart;
use App\Mail\FavToEnd;
use App\Mail\OfferAccepted;
use App\Mail\OfferReceived;
use App\Mail\OfferRejected;
use App\Mail\Contact;

use Illuminate\Support\Facades\Http;

class AuthController extends ApiController
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            $this->messages[] = 'Wrong credentials';
        }
        else
        {
            /*$user = User::where('email',$request->email)->first();
            $token = JWTAuth::fromUser($user);

            $this->response = [
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'token' => 'Bearer ' . $token
            ];
            $this->total = 1;
            $this->code = ResponseAlias::HTTP_OK;*/

            $token = auth()->attempt($validator->validated(), true);
            $user = \Auth::user();

            if (!$user)
            {
                $this->messages[] = 'Wrong credentials';
                $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            }
            else if ($user->confirmed == 0)
            {
                $this->messages[] = 'User activation pending';
                $this->code = ResponseAlias::HTTP_FORBIDDEN;
            }
            else
            {
                $user->number_login+=1;
                $user->save();

                $this->response = [
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'token' => 'Bearer ' . $token
                ];
                $this->total = 1;
                $this->code = ResponseAlias::HTTP_OK;
            }

        }

        return $this->sendResponse();
    }

    public function loginAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = 'Credenciales no válidas';
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $token = auth()->attempt($validator->validated(), true);
            $user = \Auth::user();

            if (!$user)
            {
                $this->messages[] = 'Credenciales no válidas';
                $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            }
            else if ($user->role_id != 1)
            {
                $this->messages[] = 'Credenciales no válidas';
                $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            }
            else
            {
                $user->number_login+=1;
                $user->save();

                $this->response = [
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'token' => 'Bearer ' . $token
                ];
                $this->total = 1;
                $this->code = ResponseAlias::HTTP_OK;
            }

        }

        return $this->sendResponse();
    }

    /**
     * Registers a user.
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            //'address'=>'required|string',
            //'city'=>'required|string',
            //'cp'=>'required|string',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => ['required', 'string', Password::min(6), 'confirmed'],
            //'document_number' => ['required', 'unique:users', new Dni()],
            'phone' => 'required|digits:9',
            //'birthdate' => ['required', 'before:18 years ago', 'date_format:Y-m-d'],
            //'document'=>'required|mimes:jpg,jpeg,png,pdf',  // |image solo permite imagenes
            //'document_two'=>'required|mimes:jpg,jpeg,png,pdf',
        ]);


        if ($validator->fails()){
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }else{


            if($request->hasFile("document")){
                $file = Storage::disk("public")->put("", $request->file("document"));
                $archive = Archive::create([
                    "name" => $request->file("document")->getClientOriginalName(),
                    "path" => $file
                ]);
            }

            if($request->hasFile("document_two")){
                $file = Storage::disk("public")->put("", $request->file("document_two"));
                $archive_two = Archive::create([
                    "name" => $request->file("document_two")->getClientOriginalName(),
                    "path" => $file
                ]);
            }

            $user = User::create(array_merge(
                $validator->validated(),
                [
                    'password' => bcrypt($request->password),
                    'role_id' => Role::ID_USER,
                    'province_id' => $request->province_id,
                    'country_id'=>$request->country_id,
                    'confirmed'=>1,
                    'status'=>0,
                    'birthdate'=>$request->birthdate
                    //'archive_id' => $request->hasFile("document") && $file != null ? $archive->id : '',
                    //'archive_two_id'=>$request->hasFile("document_two") && $file != null ? $archive_two->id : '',
                ]
            ));

            $token = JWTAuth::fromUser($user);

            $user->activation_token = $token;
            $user->save();

            Mail::to($user->email)
                ->send(new Welcome(
                    $user,
                    url('/cuenta-verificada/'.$token)
                ));

            $this->code = ResponseAlias::HTTP_CREATED;

            Notification::create([
                'title' => __('notifications.register.title', [
                    'username' => $user->username,
                ]),
                'subtitle' => __('notifications.register.subtitle', [
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                ]),
                'user_id' => $user->id,
                'type_id' => Notification::REGISTER,
            ]);


            /* PIPEDRIVE: Crear Personas */
            if(file_exists('testing/pipedrive.txt')){
                $file = fopen('testing/pipedrive.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'Usuario creado web y envia a pipedrive '.PHP_EOL);
                fclose($file);
            }

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
                'visible_to'=> '3' // 3 = entire company
            ])->throw()->json();

            if(file_exists('testing/pipedrive.txt')){
                $file = fopen('testing/pipedrive.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'Usuario enviado a pipedrive '.PHP_EOL);
                fwrite($file, date("d/m/Y H:i:s").'-'.'Response web: '. gettype($response) .PHP_EOL);
                fwrite($file, date("d/m/Y H:i:s").'-'.'Response web: '. $response['data']['id'].PHP_EOL);
                fwrite($file, date("d/m/Y H:i:s").'-'.'Success web: '. $response['success'] .PHP_EOL);
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
                fwrite($file, date("d/m/Y H:i:s").'-'.'Success web: '. $responseDeal['success'] .PHP_EOL);
                fwrite($file, date("d/m/Y H:i:s").'-'.'Response web: '. $responseDeal['data']['id'].PHP_EOL);
                fclose($file);
            }

            /* PIPEDRIVE: Fin creacion lead (Deal) */

        }

        return $this->sendResponse();
    }


    /**
     * Registers a user.
     */
    public function registerCampaign(Request $request)
    {
        dump($request);
        dd("Fin registerCampaign");
    }

    public function petitionRecover(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $user = User::where('email', $request->get("email"))
                ->where('confirmed', 1)
                ->whereNull('deleted_at')
                ->first();

            if (!$user)
            {
                $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            }
            else
            {
                JWTAuth::factory()->setTTL(15);
                $token = JWTAuth::fromUser($user);

                $user->recover_token = $token;
                $user->save();

                Mail::to($user->email)
                    ->send(new RecoverPassword(
                        $user,
                        url('/reestablecer-contra/'.$token)
                    )
                );

                $this->code = ResponseAlias::HTTP_OK;
            }
        }

        return $this->sendResponse();
    }

    /**
     * Resets a password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function recoverPassword(Request $request, $token)
    {
        $user = User::where('recover_token', $token)
            ->where('confirmed', 1)
            ->whereNull('deleted_at')
            ->first();

        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', Password::min(6)/*, 'confirmed'*/],
        ]);

        if (!$user || $validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $user->password = bcrypt($request->password);
            $user->recover_token = null;
            $user->save();

            $this->code = ResponseAlias::HTTP_OK;
        }

        return  $this->sendResponse();
    }

    public function resendEmail(Request $request)
    {
        $user = User::where(["email" => $request->get("email")])->first();
        $token = JWTAuth::fromUser($user);
        Mail::to($user->email)
            ->send(new Welcome($user, $token));
        return (new SuccessHandlerResponse(201, ['user' => $user]))->toArray();
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    public function deletePermanent()
    {
        $userid = \auth()->id();
        $this->deleteUser($userid);
        return (new SuccessHandlerResponse(200))->toArray();
    }

    public function deletePermanentAdmin(Request $request, $id)
    {
        $request->user()->authorizeRoles(['admin']);
        $this->deleteUser($id);
        return (new SuccessHandlerResponse(200))->toArray();
    }

    /**
     * Get the authenticated User.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile(Request $request)
    {
        return (new SuccessHandlerResponse(200, [\auth()->user()]))->toArray();
    }

    /**
     * Get the authenticated User.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request, $id)
    {
        $user = User::find($id);
        return (new SuccessHandlerResponse(200, [$user]))->toArray();
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     */
    protected function createNewToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => auth()->user()
        ];
    }

    /**
     * @param $userid
     */
    private function deleteUser($userid): void
    {
        $userAuctions = UserAuction::where("user_id", $userid)->get();
        foreach ($userAuctions as $userAuction) {
            $archives = UserAuctionArchive::select("user_auction_archives.*")
                ->join("archives", "archives.id", "=", "user_auction_archives.archives_id")
                ->where("user_auction_archives.user_auction_id", $userAuction->id)
                ->get();
            foreach ($archives as $archive) {
                $archive->delete();
                Archives::find($archive->archives_id)->delete();
            }
            $userAuctions->titular_name = "";
            $userAuctions->iban_number = "";
        }
        $user = User::find($userid);
        $user->email = uniqid('delete_') . "@delete.com";
        $user->surname = uniqid('delete_');
        $user->name = uniqid('delete_');
        $user->phone = 1234;
        $user->address = "";
        $user->document_number = uniqid();
        $user->confirmed = 0;
        $user->status = 0;
        $user->save();
        $user->delete();
    }

    public function testEmail($email = 'luiscampos@atlantelt.com', $template_id = 1)
    {

        $user = User::find(27);
        $token = 'testing';
        /*if($template_id == 1){
            print_r("TestEmail Controller function");


            Mail::to($email)
                    ->send(new Welcome(
                        $user,
                        url('/cuenta-verificada/'.$token)
                    ));
                    print_r("Email enviado");

        }else{
            print_r("TestEmail Controller function recover");
            Mail::to($user->email)
            ->send(new RecoverPassword(
                $user,
                url('/reestablecer-contra/'.$token)
            ));

        }*/

        switch ($template_id) {
			case 1: print_r("1. Bienvenida TestEmail");echo "<br>";
                    Mail::to($email)
                        ->send(new Welcome(
                        $user,
                        url('/cuenta-verificada/'.$token)
                        ));
                    break;

			case 2: print_r("2. Restablecer contraseña");echo "<br>";
                    Mail::to($email)
                        ->send(new RecoverPassword(
                        $user,
                        url('/reestablecer-contra/'.$token)
                        ));
                    break;

            case 3: print_r("3. Deposito invalidado");echo "<br>";
                    $deposit = Deposit::find(1);
                    if($deposit){
                        Mail::to($email)
                        ->send(new DepositInValid($user, $deposit));
                        print_r("Mail deposito enviado");
                    }else{
                        print_r("Deposito de ejemplo no encontrado");
                    }
                    break;

            case 4: print_r("4. Deposito validado");echo "<br>";
                    $deposit = Deposit::find(1);
                    if($deposit){
                        Mail::to($email)
                        ->send(new DepositValid($user, $deposit));
                        print_r("Mail deposito enviado");
                    }else{
                        print_r("Deposito de ejemplo no encontrado");
                    }
                    break;

            case 5: print_r("5. Representación  invalidada");echo "<br>";
                    $representation = Representation::find(1);
                    if($representation){
                        Mail::to($email)
                        ->send(new RepresentationInValid($user, $representation));
                        print_r("Mail representacion invalida enviado");
                    }else{
                        print_r("Representación de ejemplo no encontrada");
                    }
                    break;

            case 6: print_r("6. Representacion validada");echo "<br>";
                    $representation = Representation::find(1);
                    if($representation){
                        Mail::to($user->email)
                        ->send(new RepresentationValid($user, $representation));
                        print_r("Mail representacion valida enviado");
                    }else{
                        print_r("Representación de ejemplo no encontrada");
                    }
                    break;

            case 7: print_r("7. Confirmación de puja");echo "<br>";
                    $bid = Bid::find(1);
                    $auction = Auction::find(2);
                    if($bid && $auction){
                        Mail::to($user->email)
                            ->send(new SuccessBid($user, $bid, $auction));
                        print_r("Mail confirmacion de puja enviado");
                    }else{
                        print_r("Puja de ejemplo no encontrada");
                    }
                    break;

            case 8: print_r("8. Puja superada");echo "<br>";
                    $bid = Bid::find(1);
                    $auction = Auction::find(2);
                    if($bid && $auction){
                        Mail::to($user->email)
                        ->send(new DenyBid($user, $bid, $auction));
                        print_r("Mail puja superada");
                    }else{
                        print_r("Puja de ejemplo no encontrada");
                    }
                    break;

            case 9:print_r("9. Venta favorita finalizara");echo "<br>";
                    $auction = Auction::find(2);
                    Mail::to($email)
                        ->send(new FavToEnd($user, $auction));
                    print_r("Notificacion subasta favorita a punto de finalizar, enviada");
                    break;

            case 10:print_r("10. Ganador de la subasta");
                    $bid = Bid::find(1);
                    $auction = Auction::find(2);
                    Mail::to($user->email)
                        ->send(new WinBid($user, $bid, $auction));
                    print_r("Mail ganador de subasta enviado");
                    break;

            case 11:print_r("11. Venta/subasta favorita iniciada");echo "<br>";
                    $auction = Auction::find(2);
                    Mail::to($email)
                        ->send(new FavStart($user, $auction));
                    print_r("Notificacion venta favorita iniciada, enviada");
                    break;

            case 12:print_r("12. Subasta favorita finalizada");echo "<br>";
                    $auction = Auction::find(2);
                    Mail::to($email)
                        ->send(new FavEnd($user, $auction));
                    print_r("Notificacion subasta favorita finalizada, enviada");
                    break;

            case 13:print_r("13. Subasta finalizada");echo "<br>";
                    $auction = Auction::find(2);
                    Mail::to($email)
                        ->send(new AuctionEnd($user, $auction));
                    print_r("Notificacion subasta finalizada enviada");echo "<br>";
                    break;

            case 14:print_r("14. Email administrador fin subasta");echo "<br>";
                    $auction = Auction::find(2);
                    Mail::to($email)
                        ->send(new EndAdministrator($user, $auction));
                    print_r("Mail a administrador de subasta enviado");
                    break;


            case 15:print_r("15. Oferta aceptada");echo "<br>";
                    $direct_offer = DirectSaleOffer::where('user_id',$user->id);
                    Mail::to($email)
                        ->send(new OfferAccepted($user, $direct_offer));
                    print_r("Notificacion Oferta aceptada, enviada");
                    break;

            case 16:print_r("16. Oferta recibida");echo "<br>";

                    DirectSaleOffer::create(array_merge(
                        [
                            "user_id" => 27,
                            "auction_id" => 2
                        ]
                    ));

                    //$direct_offer = DirectSaleOffer::where('user_id',$user->id);
                    $direct_offer = DirectSaleOffer::with(["auction"])->where("auction_id",2)->where("user_id",27)->orderBy('id', 'desc')->first();

                    Mail::to($email)->send(new OfferReceived($user, $direct_offer , "".$direct_offer->auction->title , "".$direct_offer->auction->guid , "".$direct_offer->import , "".$direct_offer->created_at ));
                    print_r("Notificacion Oferta recibida, enviada");
                    break;

            case 17:print_r("17. Oferta rechazada");echo "<br>";
                    $direct_offer = DirectSaleOffer::where('user_id',$user->id);
                    Mail::to($email)
                        ->send(new OfferRejected($user, $direct_offer));
                    print_r("Notificacion Oferta recibida, enviada");
                    break;


            case 18: print_r("18. Contacto");echo "<br>";
                    $data = array("firstname" => "Luis", "lastname" => "Campos","email" => "luiscampos@atlantelt.com","phone" => "666333999",
                                  "subject" => "Legal", "message" => "Mensaje de prueba");
                    //$data = '';
                    Mail::to($email)
                        ->send(new Contact($data));
                    print_r("Notificacion contacto, enviado");
                break;

            default: break;
        }


        /*
        $email_base = $template_id == 1 ? 'email_base_2':'email_base';

        $tmp = Template::find($template_id);
        Mail::send($email_base, ['tmp' => $tmp, 'test'=> 1], function ($message) use($tmp,$email) {
            $message->to($email, 'PRUEBA CLIENTE');
            $message->subject($tmp->title);
        });
        print_r("Enviado template: "); print_r($tmp->id); print_r(" - ");print_r($tmp->title);
        */
    }


}
