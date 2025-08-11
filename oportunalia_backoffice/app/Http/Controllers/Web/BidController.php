<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiController;
use App\Mail\DenyBid;
use App\Mail\SuccessBid;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Deposit;
use App\Models\Notification;
use App\Models\Representation;
use App\Models\Responses\ErrorHandlerResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BidController extends ApiController
{
    /**
     * Stores data of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $guid
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $guid)
    {
        $auction = Auction::where('link_rewrite', $guid)->first();

        // return response()->json($auction,422);

        if (!$auction) {
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }

        $dateNow = new \DateTime();
        $dateStartAuction = new \DateTime($auction->start_date);
        $dateEndAuction = new \DateTime($auction->end_date);

        if ($dateNow < $dateStartAuction || $dateNow > $dateEndAuction) {
            $this->messages[] = 'Cannot bid on a auction that have finished or have not started yet';
            $this->code = 418;
            return $this->sendResponse();
        }

        if((bool)Auth::user() && (Auth::id()==27 || Auth::user()->role_id == 5)){
            $acreedor_privilegiado = true;
        }else{
            $acreedor_privilegiado = false;
        }

        if ($auction->deposit > 0 && $acreedor_privilegiado== false) {
            $deposit = Deposit::where('auction_id', $auction->id)
                ->where('user_id', auth()->user()->getAuthIdentifier())
                ->where('status', 1)
                ->first();

            if (!$deposit) {
                $this->messages[] = 'User has not submitted a deposit or it has not been approved yet';
                $this->code = 422;
                return $this->sendResponse();
            }
        }

        $validator = Validator::make($request->all(), [
            'import' => 'required|min:0',
            'representation_id' => 'nullable',
        ])
            ->after(function ($validator) use ($request) {

                if ($request->input('representation_id')) {
                    $representation = Representation::where('guid', $request->input('representation_id'))  //guid
                        ->where('user_id', auth()->user()->getAuthIdentifier())
                        ->first();

                    if (!$representation) {
                        $validator->errors()->add('representation_id', 'validation:of_user');
                    }
                }
            });

        if ($validator->fails()) {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        } else {
            $this->code = ResponseAlias::HTTP_CREATED;

            DB::beginTransaction();
            DB::raw('LOCK TABLES bids WRITE');

            $lastBid = Bid::where('auction_id', $auction->id)
                ->orderBy('id', 'desc')
                ->first();

            $bidInterval = $auction->bid_price_interval;

            if ($lastBid) {
                if (($lastBid->import + $bidInterval) > $request->import) {
                    $this->messages[] = 'Import too low';
                    $this->code = 419;
                } else {
                    $autoBid = Bid::where('auction_id', $auction->id)
                        ->orderBy('auto', 'desc')
                        ->first();

                    // If the bid intented by the user equals the current automatic bid
                    if (($autoBid->auto) && ($request->import == $autoBid->auto)) {
                        $this->messages[] = 'There\'s already an auto bid and the import is lower';
                        $this->response = $autoBid->auto;
                        $this->code = 420;
                        return $this->sendResponse();
                    }

                    $bid = Bid::create([
                        "auction_id" => $auction->id,
                        "user_id" => auth()->user()->getAuthIdentifier(),
                        "representation_id" => $request->input('representation_id', null),
                        "import" => $request->input("auto", 0) ? ($lastBid->auto ? $lastBid->auto : $lastBid->import) + $bidInterval : $request->import,
                        "auto" => $request->input("auto", 0) ? $request->import : 0.00,
                    ]);

                    $this->updateAuctionTime($auction, $dateEndAuction, $dateNow);

                    $auction->parseForEmail();
                    $bid->parseForEmail();
                    DB::commit();
                    $this->sendEmailConfirmation($bid->user_id, $bid, $auction);
                    $this->sendEmailLast($lastBid->user_id, $bid, $auction);
                    $this->newNotification(auth()->user(), $request->import, $auction);


                    $autoBid = Bid::where('auction_id', $auction->id)
                        ->orderBy('auto', 'desc')
                        ->first();
                    $auction = Auction::where('link_rewrite', $guid)->first();
                    //If there is an automatic bid placed tbid && import is lower than automatic bid placed
                    if (($autoBid->auto) && ($request->import < $autoBid->auto)) {
                        $this->checkAutoBid($auction, $autoBid, $request->import, $bid);
                    }
                }
            } else {
                if ($auction->minimum_bid > 0 && $auction->minimum_bid > $request->input('import')) {
                    $this->messages[] = 'Lower than minimum bid';
                    $this->code = 421;
                } else {
                    // If its automatic bid
                    if ($request->input("auto", 0)) {

                        $bid = Bid::create([
                            "auction_id" => $auction->id,
                            "user_id" => auth()->user()->getAuthIdentifier(),
                            "representation_id" => $request->input('representation_id', null),
                            "import" => $auction->bid_price_interval + 1,
                            "auto" => $request->input("auto", 0) ? $request->import : 0.00,
                        ]);

                        $this->updateAuctionTime($auction, $dateEndAuction, $dateNow);
                        DB::commit();
                        $auction->parseForEmail();
                        $bid->parseForEmail();

                        $this->sendEmailConfirmation($bid->user_id, $bid, $auction);
                        $this->newNotification(auth()->user(), $request->import, $auction);

                    } else { // If its manual bid

                        $bid = Bid::create([
                            "auction_id" => $auction->id,
                            "user_id" => auth()->user()->getAuthIdentifier(),
                            "representation_id" => $request->input('representation_id', null),
                            "import" => $request->input("auto", 0) ?
                                ($auction->minimum_bid > 0 ? $auction->minimum_bid : $request->input('import'))
                                : $request->input('import'),
                            "auto" => $request->input("auto", 0) ? $request->input('import') : 0.00,
                        ]);

                        $this->updateAuctionTime($auction, $dateEndAuction, $dateNow);
                        DB::commit();
                        $auction->parseForEmail();
                        $bid->parseForEmail();

                        $this->sendEmailConfirmation($bid->user_id, $bid, $auction);
                        $this->newNotification(auth()->user(), $request->import, $auction);

                    }
                }
            }

        }

        return $this->sendResponse();
    }

    private function newNotification($user, $amount, $auction)
    {
        Notification::create([
            'title' => __('notifications.bid.title', [
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
            ]),
            'subtitle' => __('notifications.bid.subtitle', [
                'amount' => $amount,
            ]),
            'user_id' => $user->id,
            'auction_id' => $auction->id,
            'type_id' => Notification::BID,
        ]);
    }

    private function updateAuctionTime($auction, $dateEndAuction, $dateNow)
    {
        $intervalAuctions = date_interval_create_from_date_string(($auction->bid_time_interval ?? 60) . ' seconds');
        $dateEndAuction->sub($intervalAuctions);
        if ($dateNow > $dateEndAuction) {
            $newDate = new \DateTime($auction->end_date);
            $newDate->add($intervalAuctions);
            $auction->end_date = $newDate;
            $auction->save();
        }
    }

    private function sendEmailLast($user_id, $bid, $auction)
    {
        $user = User::find($user_id);
        Mail::to($user->email)
            ->send(new DenyBid($user, $bid, $auction));
    }

    private function sendEmailConfirmation($user_id, $bid, $auction)
    {
        $user = User::find($user_id);
        Mail::to($user->email)
            ->send(new SuccessBid($user, $bid, $auction));
    }

    private function checkAutoBid($auction, $autoBid, $import, $lastBid)
    {

        $dateNow = new \DateTime();
        $dateEndAuction = new \DateTime($auction->end_date);
        DB::beginTransaction();
        DB::raw('LOCK TABLES bids WRITE');
        $bid = Bid::create([
            "auction_id" => $auction->id,
            "user_id" => $autoBid->user_id,
            "representation_id" => $autoBid->representation_id,
            "import" => $auction->bid_price_interval + $import,
            "auto" => $autoBid->auto,
        ]);

        $this->updateAuctionTime($auction, $dateEndAuction, $dateNow);
        DB::commit();

        $auction->parseForEmail();
        $bid->parseForEmail();

        $user = User::find($autoBid->user_id);

        $this->sendEmailConfirmation($bid->user_id, $bid, $auction);
        $this->sendEmailLast($lastBid->user_id, $bid, $auction);
        $this->newNotification($user, $auction->bid_price_interval + $import, $auction);
    }
}
