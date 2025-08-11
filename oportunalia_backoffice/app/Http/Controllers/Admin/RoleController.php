<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RoleController extends ApiController
{
    public function list(Request $request)
    {
        $this->code = ResponseAlias::HTTP_OK;
        $roles=Role::all();
        $this->response=$roles;
        $this->total=$roles->count();
        return $this->sendResponse();
    }
}
