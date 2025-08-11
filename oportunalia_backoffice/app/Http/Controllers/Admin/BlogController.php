<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Resources\ActiveCategoryResource;
use App\Models\ActiveCategory;
use App\Models\Archive;
use App\Models\Blog;
use App\Models\Role;
use App\Models\User;
use App\Rules\Dni;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BlogController extends ApiController
{
    /**
     * Returns a list of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listAll(Request $request)
    {
        $blogQuery = Blog::select(
            "blogs.id",
            "blogs.created_at",
            "blogs.show_date",
            "blogs.publish_date",
            "blogs.title",
            "blogs.views",
            "blogs.status_id",
            "blog_statuses.name as status",
        )
        ->join("blog_statuses", "blog_statuses.id", "=", "blogs.status_id");

        $blogQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('title', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('content', 'LIKE', '%' . $request->input('search') . '%');
        });

        $blogQuery->when($request->has('status_id'), function (Builder $builder) use ($request) {
            $builder->where('status_id', '=', $request->input('status_id'));
        });

        $blogQuery->when($request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $blogs = $blogQuery->get();

        $this->response = $blogs;
        $this->total = $blogs->count();
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
            'title' => 'required|string',
            'content' => 'required|string',
            'show_date' => 'nullable|date_format:Y-m-d H:i|after:now',
            'status_id' => 'nullable',
        ];

        if ($request->get('status_id') == 1) { $rules['show_date'] = 'nullable'; }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $values = $validator->validated();

            $values['guid'] = (string) Str::uuid();

            switch ($values['status_id'])
            {
                case 1: $values['publish_date'] = date('Y-m-d H:i:s'); break;
                case 2: $values['status_id'] = $values['show_date'] ? 3 : 2;
            }

            Blog::create($values);
            $this->code = ResponseAlias::HTTP_CREATED;
        }

        return $this->sendResponse();
    }

    /**
     * Retrieves data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request, $id)
    {
        $blog = Blog::select("*")->where("id", $id)->first();

        $blog->public_path = url('/actualidad/'.$blog->guid);

        $this->response = $blog;
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

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
        Blog::find($id)->delete();
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();
    }

    /**
     * Updates data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $stored = Blog::find($id);

        $rules = [
            'title' => 'required|string',
            'content' => 'required|string',
            'show_date' => 'nullable|date_format:Y-m-d H:i|after:now',
            'status_id' => 'required',
        ];

        if ($request->get('status_id') == 1) { $rules['show_date'] = 'nullable'; }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $values = $validator->validated();

            switch ($values['status_id'])
            {
                case 1:
                    $values['show_date'] = null;
                    if (!$stored->publish_date) { $values['publish_date'] = date('Y-m-d H:i:s'); }
                    break;
                case 2:
                    if ($stored->publish_date) { $values['publish_date'] = null; }
                    $values['status_id'] = $values['show_date'] ? 3 : 2;
                    break;
            }

            $stored->update($values);
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
            __('fields.backoffice.blog'),
        ];

        switch ($type)
        {
            case 'xlsx':

                $filename = composeFilename($filename, 'xlsx');

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\Admin\BlogExport, $filename
                );

            default:
                $this->code = ResponseAlias::HTTP_BAD_REQUEST;
                return $this->sendResponse();
        }
    }
}
