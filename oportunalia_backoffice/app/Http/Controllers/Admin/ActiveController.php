<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Resources\ActiveImagesResource;
use App\Models\Auction;
use App\Models\AuctionStatus;
use App\Models\Active;
use App\Models\ActiveCategory;
use App\Models\ActiveImages;
use App\Models\Archive;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Mail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActiveController extends ApiController
{
    function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'active_category_id' => 'required',
            'province_id' => 'required',
            'address' => 'required|string',
            'city' => 'required|string',
            'refund' => 'required|boolean',
            'lat' => 'required',
            'lng' => 'required',
            'active_condition_id' => 'required',
            'area' => 'required',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $active = Active::create($validator->validated());

            if ($request->hasFile("images"))
            {
                foreach ($request->file("images") as $image)
                {
                    $file = Storage::disk("public")->put("", $image);
                    $archive= Archive::create(["name"=>$image->getClientOriginalName(),"path"=>$file]);

                    ActiveImages::create(["archive_id" => $archive->id, "active_id" => $active->id]);
                }
            }

            $this->code = ResponseAlias::HTTP_CREATED;
        }

        return $this->sendResponse();
    }

    public function listAll(Request $request)
    {
        $activeQuery = Active::query()
            ->select(
                "actives.id",
                "actives.name",
                "actives.city",
                "actives.active_category_id",
                "actives.province_id",
                "provinces.name as province",
                "active_categories.name as active_category",
                "auctions.id as auction_id",
                "auctions.auction_type_id",
                "auctions.auction_status_id",
            )
            ->join("provinces", "actives.province_id", "=", "provinces.id")
            ->join("active_categories", "actives.active_category_id", "=", "active_categories.id")
            ->leftJoin("auctions", "actives.id", "=", "auctions.active_id")
            ->groupBy('actives.id');

        $activeQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('id', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('name', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('city', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('province', 'LIKE', '%' . $request->input('search') . '%');
        });

        $activeQuery->when($request->has('active_category_id'), function (Builder $builder) use ($request) {
            $builder->where('actives.active_category_id', '=', $request->input('active_category_id'));
        });

        $activeQuery->when($request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $active = $activeQuery->get();

        $this->response = $active;
        $this->total = $active->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    public function detail(Request $request, $id)
    {
        $active = Active::select(
            "actives.*",
            "provinces.name as province",
            "active_categories.name as active_category",
            "auctions.id as auction_id",
            "auctions.auction_type_id",
            "auctions.auction_status_id",
        )
        ->join("provinces", "actives.province_id", "=", "provinces.id")
        ->join("active_categories", "actives.active_category_id", "=", "active_categories.id")
        ->leftJoin("auctions", "actives.id", "=", "auctions.active_id")
        ->where("actives.id", "=", $id)
        ->first();

        $images = ActiveImages::with('image')->where("active_images.active_id", "=", $id)->get();

        $active->images = ActiveImagesResource::collection($images);

        $this->response = $active;
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    public function edit(Request $request, $id)
    {
        $active = Active::find($id);

        if (!$active)
        {
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }

        $auction = Auction::where('active_id', $id)->first();

        /*if ($auction && !in_array($auction->auction_status_id, [AuctionStatus::SOON, AuctionStatus::DRAFT, AuctionStatus::ONGOING]))
        {
            $this->messages[] = 'Cannot edit an active with a finished auction or direct sale';
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            return $this->sendResponse();
        }*/

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'active_category_id' => 'required',
            'province_id' => 'required',
            'address' => 'required|string',
            'city' => 'required|string',
            'refund' => 'required|boolean',
            'lat' => 'required',
            'lng' => 'required',
            'active_condition_id' => 'required',
            'area' => 'required',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            Active::find($id)->update($validator->validated());

            if ($request->hasFile("images"))
            {
                foreach ($request->file("images") as $image) {
                    $file = Storage::disk("public")->put("", $image);
                    $archive= Archive::create(["name"=>$image->getClientOriginalName(),"path"=>$file]);
                    ActiveImages::create(["archive_id" => $archive->id, "active_id" => $id]);
                }
            }

            $this->code = ResponseAlias::HTTP_CREATED;
        }

        return $this->sendResponse();
    }

    public function delete(Request $request, $id)
    {
        $active = Active::find($id);
        $images = ActiveImages::where("active_id","=",$id);
        $active->delete();
        $this->deleteImages($images);
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();

    }

    public function deleteImage(Request $request, $id)
    {
        $image=ActiveImages::find($id);
        $image->delete();
        $this->deleteImages([$image]);
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();

    }

    private function deleteImages($images)
    {
        foreach ($images as $image) {

            Storage::disk("public")->delete(  $image->image()->first()->path);
            $image->image()->delete();
        }
    }


    public function duplicate(Request $request, $id)
    {
        $active = Active::find($id);

        $new_active = $active->replicate();
        $new_active->name = $new_active->name . " - copia";
        $new_active->created_at = Carbon::now();
        $new_active->updated_at = Carbon::now();

        $new_active->save();

        /* Duplicamos imagenes */
        $images = ActiveImages::with('image')->where("active_images.active_id", "=", $id)->get();
        if(count($images)){
            foreach($images as $key=>$value){
                $new_image = new ActiveImages();
                $new_image->active_id = $new_active->id;
                $new_image->created_at = Carbon::now();
                $new_image->updated_at = Carbon::now();
                $new_image->archive_id = $value->archive_id;
                $new_image->save();
            }
        }

        $this->response = $new_active;
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    /**
     * Export the resource to a file.
     *
     * @param  string  $type
     * @return any
     */
    public function export($type)
    {
        $filename = [
            config('app.name'),
            __('fields.backoffice.actives'),
        ];

        switch ($type)
        {
            case 'xlsx':

                $filename = composeFilename($filename, 'xlsx');

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\Admin\ActiveExport, $filename
                );

            default:
                $this->code = ResponseAlias::HTTP_BAD_REQUEST;
                return $this->sendResponse();
        }
    }

    public function maxId(){

        $idMax = DB::table('auctions')
        ->select(DB::raw('max(id) as last'))
        ->get();

        $idMax = intval($idMax[0]->last + 1);

        return $idMax;
    }
}
