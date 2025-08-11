<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\NewsletterTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class NewsletterTemplateController extends ApiController
{
    /**
     * Returns a list of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listAll(Request $request)
    {
        $templateQuery = NewsletterTemplate::select(
            "newsletter_templates.id",
            "newsletter_templates.created_at",
            "newsletter_templates.name",
            "newsletter_templates.subject",
            "newsletter_templates.sender",
            "newsletter_templates.email",
        );

        $templateQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('name', 'LIKE', '%' . $request->input('search') . '%')
            ->orWhere('subject', 'LIKE', '%' . $request->input('search') . '%');
        });

        $templateQuery->when($request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $templates = $templateQuery->get();

        $this->response = $templates;
        $this->total = $templates->count();
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'subject' => 'required|string',
            'sender' => 'required|string',
            'email' => 'required|string|email|max:100',
            'content' => 'required|string',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            NewsletterTemplate::create($validator->validated());
            $this->code = ResponseAlias::HTTP_CREATED;
        }

        return $this->sendResponse();
    }

    /**
     * Retrieves data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request, $id)
    {
        $template = NewsletterTemplate::select("*")->where("id", $id)->first();
        $this->response = $template;
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();
    }

    /**
     * Deletes data of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        NewsletterTemplate::find($id)->delete();
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'subject' => 'required|string',
            'sender' => 'required|string',
            'email' => 'required|string|email|max:100',
            'content' => 'required|string',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            NewsletterTemplate::find($id)->update($validator->validated());
            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }
}
