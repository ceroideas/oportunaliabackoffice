<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\ApiController;
use App\Models\AuctionType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuctionTypeController extends ApiController
{
    public function list(Request $request)
    {
        $this->code = ResponseAlias::HTTP_OK;
        $response_list=AuctionType::all();
        $this->response=$response_list;
        $this->total=$response_list->count();
        return $this->sendResponse();
    }
}
