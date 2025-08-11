<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Resources\RepresentationResource;
use App\Mail\RepresentationInValid;
use App\Mail\RepresentationValid;
use App\Models\Archive;
use App\Models\Representation;
use App\Models\User;
use App\Rules\Dni;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RepresentationController extends ApiController
{
    /**
     * Returns a list of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $representationsQuery = Representation::with("image")
            ->select(
                "representations.*",
                "representation_types.name as representation_type",
                "countries.name as country", "provinces.name as province"
            )
            ->join("representation_types", "representation_types.id", "=", "representation_type_id")
            ->join("countries", "countries.id", "=", "country_id")
            ->join("provinces", "provinces.id", "=", "province_id");

        $representationsQuery->when( $request->has('search'), function (Builder $builder) use ($request) {
            $builder->where('alias', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('firstname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('lastname', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('address', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('city', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('cp', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('countries.name', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('provinces.name', 'LIKE', '%' . $request->input('search') . '%');
        });

        $representationsQuery->when( $request->has('status'), function (Builder $builder) use ($request) {
            $builder->where('status', '=', $request->input('status'));
        });

        $representationsQuery->when( $request->has('representation_type_id'), function (Builder $builder) use ($request) {
            $builder->where('representation_type_id', '=', $request->input('representation_type_id'));
        });

        $representationsQuery->when( $request->has('order'), function (Builder $builder) use ($request) {
            $builder->orderBy(explode("__", $request->input('order'))[0], explode("__", $request->input('order'))[1]);
        });

        $representations = $representationsQuery->get();

        $this->response = RepresentationResource::collection($representations);
        $this->total = $representations->count();
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
            'alias' => 'required|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'document_number' => ['required', new Dni()],
            'file' => 'nullable|mimes:jpg,jpeg,png,pdf',
            'user_id' => 'required',
            'address' => !$request->get("use_user_address") ? 'required|string' : '',
            'city' => !$request->get("use_user_address") ? 'required|string' : '',
            'province_id' => !$request->get("use_user_address") ? 'required' : '',
            'country_id' => !$request->get("use_user_address") ? 'required' : '',
            'cp' => !$request->get("use_user_address") ? 'required|string' : '',
            'representation_type_id' => 'required',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $file = Storage::disk("public")->put("", $request->file("file"));
            $archive = Archive::create([
                "name" => $request->file("file")->getClientOriginalName(),
                "path" => $file
            ]);

            $values = array_merge(
                Arr::except($validator->validated(), 'file'),
                [
                    'guid' => (string) Str::uuid(),
                    'archive_id' => $archive->id,
                ]
            );

            $user = User::find($request->get("user_id"));

            if ($request->get("use_user_address", false)) {
                if ($user->address == null || $user->province_id == null ||
                    $user->country_id == null || $user->cp == null || $user->city == null)
                {
                    $this->code = 452;

                    return $this->sendResponse();
                }

                $values["address"] = $user->address;
                $values["province_id"] = $user->province_id;
                $values["country_id"] = $user->country_id;
                $values["cp"] = $user->cp;
                $values["city"] = $user->city;
            }

            Representation::create($values);

            $this->code = ResponseAlias::HTTP_CREATED;
        }

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
            'alias' => 'required|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'document_number' => ['required', new Dni()],
            'file' => 'nullable|mimes:jpg,jpeg,png,pdf',
            'user_id' => 'required',
            'address' => !$request->get("use_user_address") ? 'required|string' : '',
            'city' => !$request->get("use_user_address") ? 'required|string' : '',
            'province_id' => !$request->get("use_user_address") ? 'required' : '',
            'country_id' => !$request->get("use_user_address") ? 'required' : '',
            'cp' => !$request->get("use_user_address") ? 'required|string' : '',
            'use_user_address' => 'required|boolean',
            'representation_type_id' => 'required',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            $values = $validator->validated();

            if ($request->file("file"))
            {
                $file = Storage::disk("public")->put("", $request->file("file"));
                $archive= Archive::create(["name"=>$request->file("file")->getClientOriginalName(),"path"=>$file]);

                $values = array_merge(
                    $values,
                    ['archive_id' => $archive->id]
                );
            }

            $user = User::find($request->get("user_id"));

            if ($request->get("use_user_address"))
            {
                if ($user->address == null || $user->province_id == null || $user->country_id == null || $user->cp == null || $user->city == null)
                {
                    $this->code = 452;
                    return $this->sendResponse();
                }

                $values["address"] = $user->address;
                $values["province_id"] = $user->province_id;
                $values["country_id"] = $user->country_id;
                $values["cp"] = $user->cp;
                $values["city"] = $user->city;
            }

            Representation::find($id)->update(Arr::except($values, 'file'));

            $this->code = ResponseAlias::HTTP_CREATED;
        }

        return $this->sendResponse();
    }

    function delete(Request $request, $id)
    {
        $representation = Representation::find($id);
        $archive= Archive::find($representation->archive_id);
        if (isset($archive) && !empty($archive->path)) {
            Storage::disk("public")->delete($archive->path);
        }
        if (isset($representation)) {
            $representation->delete();
        }
        if (isset($archive)) {
            $archive->delete();
        }
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();
    }

    /**
     * Validation of a representation.
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
            $representation = Representation::find($id);
            $representation->status = $request->status;
            $representation->save();

            if ($request->status == 1) {
                $this->sendEmailValidated($representation);
            } else if ($request->status == 2) {
                $this->sendEmailInValidated($representation);
            }

            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }

    /**
     * Sends an email notification.
     *
     * @param  \App\Models\Representation  $representation
     */
    private function sendEmailValidated($representation)
    {
        $user = User::find($representation->user_id);
        Mail::to($user->email)
            ->send(new RepresentationValid($user, $representation));
    }

    /**
     * Sends an email notification.
     *
     * @param  \App\Models\Representation  $representation
     */
    private function sendEmailInValidated($representation)
    {
        $user = User::find($representation->user_id);
        Mail::to($user->email)
            ->send(new RepresentationInValid($user, $representation));
    }

    /**
     * Shows a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request, $id)
    {
        $this->code = ResponseAlias::HTTP_OK;
        $representation = Representation::with("image")->select("*")->where("id", "=", $id)->first();
        $this->response = RepresentationResource::make($representation);
        $this->total = 1;
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
            __('fields.backoffice.representations'),
        ];

        switch ($type)
        {
            case 'xlsx':

                $filename = composeFilename($filename, 'xlsx');

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\Admin\RepresentationExport, $filename
                );

            default:
                $this->code = ResponseAlias::HTTP_BAD_REQUEST;
                return $this->sendResponse();
        }
    }
}
