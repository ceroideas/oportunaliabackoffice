<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Resources\ActiveCategoryResource;
use App\Models\ActiveCategory;
use App\Models\Archive;
use App\Models\Membresia;
use App\Models\Role;
use App\Models\User;
use App\Models\Auction;
use App\Rules\Dni;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class MembresiaController extends ApiController
{
    /**
     * Returns a list of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listAll(Request $request)
    {
        $membresiaQuery = Membresia::select(
            "membresias.id",
            "membresias.note",
            "membresias.auction_id",
            "membresias.user_id",
            "auctions.title",
            "users.username",
        )->join("auctions", "auctions.id", "=", "membresias.auction_id")
        ->join("users", "users.id", "=", "membresias.user_id");

        $membresias = $membresiaQuery->get();

        $this->response = $membresias;
        $this->total = $membresias->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    /**
     * Stores data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $rules = [
            'auction_id' => 'required',
            'note' => 'required|string',
            'user_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $values = $validator->validated();

            Membresia::create($values);
            $this->code = ResponseAlias::HTTP_CREATED;
        }

        return $this->sendResponse();
    }

    /**
     * Deletes data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        Membresia::find($id)->delete();
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();
    }


        /**
     * Returns a list of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function users(Request $request)
    {
        $usersMembresiaQuery = User::select(
            "users.id",
            "users.username",
        )->orderBy("username", "ASC");

        $users = $usersMembresiaQuery->get();

        $this->response = $users;
        $this->total = $users->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }


            /**
     * Returns a list of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function auctions(Request $request)
    {
        $usersMembresiaQuery = Auction::select(
            "auctions.id",
            "auctions.title",
        )->orderBy("title", "ASC");

        $users = $usersMembresiaQuery->get();

        $this->response = $users;
        $this->total = $users->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }
}
