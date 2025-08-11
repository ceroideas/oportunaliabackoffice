<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\ActiveCondition;
use App\Models\Role;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ActiveConditionController extends ApiController
{
    public function list(Request $request)
    {
        $this->code = ResponseAlias::HTTP_OK;
        $activeConditions=ActiveCondition::all();
        $this->response=$activeConditions;
        $this->total=$activeConditions->count();
        return $this->sendResponse();
    }
}
