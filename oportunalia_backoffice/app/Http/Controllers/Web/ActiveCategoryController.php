<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiController;
use App\Models\ActiveCategory;
use App\Models\Auction;
use App\Models\Role;
use App\Models\User;
use App\Rules\Dni;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Illuminate\Support\Facades\DB;


class ActiveCategoryController extends ApiController
{
    public function list(Request $request)
    {
        /*$categories = ActiveCategory::select(
            "active_categories.id",
            "active_categories.name",
        )
        ->get();*/

        /* Filtramos por categorias activas asociadas a un activo*/
        $categories = DB::table('actives')
            ->selectRaw('DISTINCT(actives.active_category_id) as id, active_categories.name as name')
            ->join("active_categories", "actives.active_category_id", "=", "active_categories.id")
            ->get();

        $this->response = $categories;
        $this->total = $categories->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }
}
