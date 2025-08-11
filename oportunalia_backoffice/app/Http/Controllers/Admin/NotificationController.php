<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Resources\NotificationAuctionResource;
use App\Http\Resources\NotificationResource;
use App\Models\Blog;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

use DB;

class NotificationController extends ApiController
{
    /**
     * Returns a list of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listAll(Request $request)
    {
        $registers = Notification::select(
            'notifications.id',
            'notifications.title',
            'notifications.subtitle',
            'notifications.user_id',
            'notifications.created_at',
            'notifications.status',
            'notifications.type_id',
            'notification_types.name as type',
        )
        ->join('notification_types', 'notification_types.id', '=', 'notifications.type_id')
        ->where('type_id', Notification::REGISTER)
        ->where('status', '!=', 2)
        ->where('notifications.created_at', '>=', date('Y-m-d', strtotime(date('Y-m-d') . ' -7 days')))
        ->latest()
        ->get();

        foreach ($registers as $register)
        {
            // GMT+2 (Europe/Madrid) time correction
            $register->created_at = date('Y-m-d H:i:s', strtotime($register->created_at . ' +2 hour'));
        }

        $documents = Notification::with(['document'])->select(
            'notifications.id',
            'notifications.title',
            'notifications.subtitle',
            'notifications.user_id',
            'notifications.auction_id',
            'notifications.representation_id',
            'notifications.created_at',
            'notifications.status',
            'notifications.type_id',
            'notification_types.name as type',
        )
        ->join('notification_types', 'notification_types.id', '=', 'notifications.type_id')
        ->whereIn('type_id', [
            Notification::DOCUMENT,
            Notification::DEPOSIT,
            Notification::REPRESENTATION,
        ])
        ->where('status', '!=', 2)
        ->where('notifications.created_at', '>=', date('Y-m-d', strtotime(date('Y-m-d') . ' -7 days')))
        ->latest()
        ->get();

        foreach ($documents as $document)
        {
            // GMT+2 (Europe/Madrid) time correction
            $document->created_at = date('Y-m-d H:i:s', strtotime($document->created_at . ' +2 hour'));
        }

        $documents = NotificationResource::collection($documents);

        $auctions = Notification::select(
            'notifications.id',
            'notifications.title',
            'notifications.subtitle',
            'notifications.user_id',
            'notifications.auction_id',
            'notifications.created_at',
            'notifications.status',
            'notifications.notification_status',
            'notifications.type_id',
            'notification_types.name as type',
            'auctions.id as reference',
            'auctions.auction_type_id',
            DB::raw("
                CASE 
                    WHEN auctions.auction_type_id = 1 THEN 
                        (SELECT MAX(bids.import) FROM bids WHERE bids.auction_id = auctions.id) 
                    ELSE 
                        (SELECT MAX(direct_sale_offers.import) FROM direct_sale_offers WHERE direct_sale_offers.auction_id = auctions.id) 
                END as __max_bid
            ")
        )
        ->join('notification_types', 'notification_types.id', '=', 'notifications.type_id')
        ->join('auctions', 'auctions.id', '=', 'notifications.auction_id')

        ->leftJoin("direct_sale_offers", "auctions.id", "=", "direct_sale_offers.auction_id")
        ->leftJoin("bids", "auctions.id", "=", "bids.auction_id")


        ->whereIn('type_id', [
            Notification::BID,
            Notification::AUCTION_END_WIN,
            Notification::AUCTION_END,
            Notification::OFFER,
        ])
        ->where('notifications.status', '!=', 2)
        ->where('notifications.created_at', '>=', date('Y-m-d', strtotime(date('Y-m-d') . ' -7 days')))
        ->whereNull('notifications.notification_status')
        ->latest()
        ->get()
        ->unique('auction_id');

       foreach ($auctions as $auction)
        {
            // GMT+2 (Europe/Madrid) time correction
            $auction->created_at = date('Y-m-d H:i:s', strtotime($auction->created_at . ' +2 hour'));
        }

        $auctions = NotificationAuctionResource::collection($auctions);

        $this->response = [
            'registers' => $registers,
            'documents'=> $documents,
            'auctions' => $auctions,
        ];
        $this->total = count($registers) + count($documents) + count($auctions);
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    /**
     * Marks a notification as seen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function status(Request $request, $id)
    {
        Notification::where('id', $id)
            ->where('status', 0)
            ->update(['status' => 1]);

        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    /**
     * Marks notifications as seen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function statusAll(Request $request, $type)
    {
        $auction_id = $request->has('auction_id') ? $request->get('auction_id') : null;

        switch ($type)
        {
            case 'registers':
                Notification::where('type_id', Notification::REGISTER)
                    ->where('status', 0)
                    ->update(['status' => 1]);
                break;

            case 'documents':
                Notification::whereIn('type_id', [
                        Notification::DOCUMENT,
                        Notification::DEPOSIT,
                        Notification::REPRESENTATION,
                    ])
                    ->where('status', 0)
                    ->update(['status' => 1]);
                break;

            case 'auctions':
                Notification::whereIn('type_id', [
                        Notification::BID,
                        Notification::AUCTION_END_WIN,
                        Notification::AUCTION_END,
                    ])
                    ->where('status', 0)
                    ->update(['status' => 1]);
                break;

            case 'bids':

                if ($auction_id) {
                    Notification::whereIn('type_id', [
                            Notification::BID,
                            Notification::OFFER,
                            Notification::AUCTION_END_WIN,
                            Notification::AUCTION_END,
                        ])
                        ->where('status', 0)
                        ->where('auction_id', $auction_id)
                        ->update(['status' => 1]);
                    break;
                }
        }

        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }
}
