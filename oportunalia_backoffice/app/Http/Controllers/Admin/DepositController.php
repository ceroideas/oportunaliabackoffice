<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Resources\DepositResource;
use App\Mail\DepositInValid;
use App\Mail\DepositValid;
use App\Mail\FavToEnd;
use App\Models\Deposit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DepositController extends ApiController
{
    /**
     * Validation of a deposit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function validate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|integer|between:1,3',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $deposit = Deposit::with(['auction'])->find($id);
            $deposit->status = $request->status;
            $deposit->save();

            if ($request->status == 1) {
                $this->sendEmailValidated($deposit);
            } else if ($request->status == 2) {
                $this->sendEmailInValidated($deposit);
            }

            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }

    /**
     * Sends an email notification.
     *
     * @param  \App\Models\Deposit  $deposit
     */
    private function sendEmailValidated($deposit)
    {
        $user = User::find($deposit->user_id);
        Mail::to($user->email)
            ->send(new DepositValid($user, $deposit));
    }

    /**
     * Sends an email notification.
     *
     * @param  \App\Models\Deposit  $deposit
     */
    private function sendEmailInValidated($deposit)
    {
        $user = User::find($deposit->user_id);
        Mail::to($user->email)
            ->send(new DepositInValid($user, $deposit));
    }

    /**
     * Returns a list of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $depositsQuery = Deposit::with(["document"])
            ->select(
                "deposits.id",
                "deposits.status",
                "deposits.archive_id",
                "deposits.created_at",
                "deposits.deposit",
                "auctions.id as reference",
                "users.username",
                "users.firstname",
                "users.lastname",
                "users.document_number"
            )
            ->join("auctions", "auctions.id", "=", "deposits.auction_id")
            ->join("users", "users.id", "=", "deposits.user_id");


        $depositsQuery->when($request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('reference', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.firstname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.lastname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('users.document_number', 'LIKE', '%' . $request->input('search') . '%');
        });
        $depositsQuery->when($request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $deposits = $depositsQuery->get();

        $this->response = DepositResource::collection($deposits);
        $this->total = $deposits->count();
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
            __('fields.backoffice.deposits'),
        ];

        switch ($type)
        {
            case 'xlsx':

                $filename = composeFilename($filename, 'xlsx');

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\Admin\DepositExport, $filename
                );

            default:
                $this->code = ResponseAlias::HTTP_BAD_REQUEST;
                return $this->sendResponse();
        }
    }
}
