<?php

namespace App\Http\Controllers;

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
            "blogs.guid",
            "blogs.title",
            "blogs.publish_date",
            "blogs.content",
        )
        ->where('blogs.status_id', 1);

        $blog = $blogQuery->get();

        $this->response = $blog;
        $this->total = $blog->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    /**
     * Retrieves data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $guid
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request, $guid)
    {
        $blog = Blog::select(
            "blogs.title",
            "blogs.publish_date",
            "blogs.content",
        )
        ->where("guid", $guid)
        ->first();

        $this->response = $blog;
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }
}
