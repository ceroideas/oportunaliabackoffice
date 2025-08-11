<?php
namespace App\Http\Controllers\Admin;


use App\Http\Controllers\ApiController;
use App\Models\AuctionStatus;
use App\Models\AuctionType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuctionStatusController extends ApiController
{
    public function list(Request $request)
    {
        $this->code = ResponseAlias::HTTP_OK;
        $response_list=AuctionStatus::take(4)->get();
        $this->response=$response_list;
        $this->total=$response_list->count();
        return $this->sendResponse();
    }
    public function listPayment(Request $request)
    {
        $this->code = ResponseAlias::HTTP_OK;
        $response_list=AuctionStatus::whereKeyNot([3,4])->get();
        $this->response=$response_list;
        $this->total=$response_list->count();
        return $this->sendResponse();
    }

    public function listPaymentCesion(Request $request)
    {
        $this->code = ResponseAlias::HTTP_OK;
        $response_list=AuctionStatus::whereKeyNot([3,4])->get();
        $this->response=$response_list;
        $this->total=$response_list->count();
        return $this->sendResponse();
    }



}
