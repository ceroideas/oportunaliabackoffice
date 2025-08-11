<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Auction;
use App\Models\Newsletter;
use App\Models\NewsletterAuction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class NewsletterController extends ApiController
{
    /**
     * Returns a list of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listAll(Request $request)
    {
        $newsletterQuery = Newsletter::select(
            "newsletters.id",
            "newsletters.created_at",
            "newsletters.subject",
            "newsletters.sender",
            "newsletters.email",
            "newsletters.send_date",
            "newsletters.sent_date",
            "newsletters.status_id",
            "newsletter_templates.name as template",
            "newsletter_statuses.name as status",
        )
        ->join("newsletter_statuses", "newsletter_statuses.id", "=", "newsletters.status_id")
        ->join("newsletter_templates", "newsletter_templates.id", "=", "newsletters.template_id");

        $newsletterQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('subject', 'LIKE', '%' . $request->input('search') . '%');
        });

        $newsletterQuery->when($request->has('template_id'), function (Builder $builder) use ($request) {
            $builder->where('template_id', '=', $request->input('template_id'));
        });

        $newsletterQuery->when($request->has('status_id'), function (Builder $builder) use ($request) {
            $builder->where('status_id', '=', $request->input('status_id'));
        });

        $newsletterQuery->when($request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $newsletters = $newsletterQuery->get();

        $this->response = $newsletters;
        $this->total = $newsletters->count();
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
            'template_id' => 'required',
            'subject' => 'required|string',
            'sender' => 'required|string',
            'email' => 'required|string|email|max:100',
            'send_date' => 'nullable|date_format:Y-m-d H:i|after:now',
            'content' => 'required|string',
            'status_id' => 'nullable',
            'auctions' => 'required',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $created = Newsletter::create($validator->validated());

            $order = 1;
            foreach ($request->get('auctions') as $auction_id)
            {
                NewsletterAuction::create([
                    'newsletter_id' => $created->id,
                    'auction_id' => $auction_id,
                    'order' => $order++,
                ]);
            }

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
        $newsletter = Newsletter::select("*")->where("id", $id)->first();
        $newsletter->auctions = Auction::select(
            "auctions.id",
            "auctions.title",
            "auctions.start_date",
            "auctions.end_date",
        )
        ->join("newsletter_auctions", "newsletter_auctions.auction_id", "=", "auctions.id")
        ->where("newsletter_id", $id)
        ->orderBy("order", "asc")
        ->get();
        $this->response = $newsletter;
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
        Newsletter::find($id)->delete();
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
            'template_id' => 'required',
            'subject' => 'required|string',
            'sender' => 'required|string',
            'email' => 'required|string|email|max:100',
            'send_date' => 'nullable|date_format:Y-m-d H:i|after:now',
            'content' => 'required|string',
            'status_id' => 'nullable',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            Newsletter::find($id)->update($validator->validated());

            NewsletterAuction::where('newsletter_id', $id)->delete();

            $order = 1;
            foreach ($request->get('auctions') as $auction_id)
            {
                NewsletterAuction::create([
                    'newsletter_id' => $id,
                    'auction_id' => $auction_id,
                    'order' => $order++,
                ]);
            }

            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }
}
