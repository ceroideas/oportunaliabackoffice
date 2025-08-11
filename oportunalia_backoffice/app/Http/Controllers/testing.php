<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

use App\Models\Province;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class testing extends ApiController
{
    public function testconection(Request $request, $id = 1){

        if (!is_numeric($id))
        {
            $this->code = ResponseAlias::HTTP_FORBIDDEN;
        }
        else
        {
            $provinces = Province::where("country_id", $id)->get();
            $this->response = $provinces;
            $this->total = $provinces->count();
            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
        //return view('testing')->with('msj','/testing index');
    }



}
