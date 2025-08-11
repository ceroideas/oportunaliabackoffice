<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\RepresentationType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RepresentationTypeController extends ApiController
{
    public function list(Request $request)
    {
        $this->code = ResponseAlias::HTTP_OK;
        $representationType=RepresentationType::all();
        $this->response=$representationType;
        $this->total=$representationType->count();
        return $this->sendResponse();
    }
}
