<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Province;
use App\Models\Role;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProvinceController extends ApiController
{
    public function list(Request $request, $id = 1)
    {
        if (!is_numeric($id))
        {
            $this->code = ResponseAlias::HTTP_FORBIDDEN;
        }
        else
        {
            $provinces = Province::where("country_id", $id)->orderBy("name","asc")->get();
            $this->response = $provinces;
            $this->total = $provinces->count();
            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }
}
