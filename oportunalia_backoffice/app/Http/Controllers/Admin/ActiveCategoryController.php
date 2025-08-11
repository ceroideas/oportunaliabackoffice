<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Resources\ActiveCategoryResource;
use App\Models\ActiveCategory;
use App\Models\Archive;
use App\Models\Role;
use App\Models\User;
use App\Rules\Dni;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use function dd;
class ActiveCategoryController extends ApiController
{
    public function listAll(Request $request)
    {
        $activeCategoryQuery = ActiveCategory::select("active_categories.id", "active_categories.name", "active_categories.description");

        $activeCategoryQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('name', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('description', 'LIKE', '%' . $request->input('search') . '%');
        });

        $activeCategoryQuery->when($request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $categories = $activeCategoryQuery->get();

        $this->response = $categories;
        $this->total = $categories->count();
        $this->code = ResponseAlias::HTTP_OK;
           

        return $this->sendResponse();
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            $this->messages[] = $validator->errors()->messages();

        } else {
            $file = Storage::disk("public")->put("", $request->file("image"));
            $archive= Archive::create(["name"=>$request->file("image")->getClientOriginalName(),"path"=>$file]);
            $activeCategory = ActiveCategory::create(array_merge(
                Arr::except($validator->validated(), "image"),
                ['archive_id' => $archive->id]

            ));
            $this->code = ResponseAlias::HTTP_CREATED;

        }
        return $this->sendResponse();

    }

    public function detail(Request $request, $id)
    {
        $this->code = ResponseAlias::HTTP_OK;
        $activeCategory = ActiveCategory::with("image")->select("*")->where("id", "=", $id)->first();
        $this->response = ActiveCategoryResource::make($activeCategory);
        $this->total = 1;
        return $this->sendResponse();
    }

    public function deleteSoft(Request $request, $id)
    {
        ActiveCategory::find($id)->delete();
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();

    }

    public function edit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
        ]);

        if ($validator->fails())
        {
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            $this->messages[] = $validator->errors()->messages();
        }
        else
        {
            $values = $validator->validated();

            if ($request->file("image")) {
                $file = Storage::disk("public")->put("", $request->file("image"));
                $archive= Archive::create(["name"=>$request->file("image")->getClientOriginalName(),"path"=>$file]);
                $values = array_merge(
                    Arr::except($validator->validated(), 'image'),
                    ['archive_id' => $archive->id],
                );
            }

            $activeCategory = ActiveCategory::find($id)->update($values);
            $this->code = ResponseAlias::HTTP_OK;
        }

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
            __('fields.backoffice.active_categories'),
        ];

        switch ($type)
        {
            case 'xlsx':

                $filename = composeFilename($filename, 'xlsx');

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\Admin\ActiveCategoryExport, $filename
                );

            default:
                $this->code = ResponseAlias::HTTP_BAD_REQUEST;
                return $this->sendResponse();
        }
    }
}
