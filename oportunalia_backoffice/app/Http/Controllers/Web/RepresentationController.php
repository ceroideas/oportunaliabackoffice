<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiController;
use App\Http\Resources\RepresentationResource;
use App\Models\Archive;
use App\Models\Notification;
use App\Models\Representation;
use App\Models\User;
use App\Rules\Dni;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RepresentationController extends ApiController
{
    public function list(Request $request)
    {
        $user = Auth::user();

        if ( !Representation::where("user_id", "=", Auth::id())->count() ) {
            
            if ($user->address && $user->province_id && $user->country_id && $user->city && $user->cp) {
                $r = new Representation;
                $r->user_id = $user->id;
                $r->guid = (string) Str::uuid();
                $r->alias = $user->firstname.' '.$user->lastname;
                $r->firstname = $user->firstname;
                $r->lastname = $user->lastname;
                $r->document_number = $user->document_number ?? "";
                $r->address = $user->address ?? "";
                $r->city = $user->city ?? "";
                $r->province_id = $user->province_id ?? "";
                $r->country_id = $user->country_id ?? "";
                $r->cp = $user->cp ?? "";
                $r->representation_type_id = 1;
                $r->save();
            }
        }


        $representations = Representation::select(
                "representations.id",
                "representations.guid",
                "representations.representation_type_id",
                "representations.alias",
                "representations.firstname",
                "representations.lastname",
                "representations.address",
                "representations.status",
                "representations.document_number",
            )
            ->where("user_id", "=", Auth::id())
            ->get();


        $this->response = $representations;
        $this->total = $representations->count();
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alias' => 'required|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'document_number' => ['required', new Dni()],
            'file' => 'required|mimes:jpg,jpeg,png,pdf',
            'address' => 'required|string',
            'city' => 'required|string',
            'province_id' => 'required',
            'country_id' => 'required',
            'cp' => 'required|string',
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
            $archive= Archive::create([
                "name" => $request->file("file")->getClientOriginalName(),
                "path" => $file
            ]);

            $representation = Representation::create(array_merge(
                Arr::except($validator->validated(), 'file'),
                [
                    'guid' => (string) Str::uuid(),
                    'archive_id' => $archive->id,
                    'user_id' => Auth::id(),
                ]
            ));

            $this->code = ResponseAlias::HTTP_CREATED;

            Notification::create([
                'title' => __('notifications.representation.title', [
                    'firstname' => Auth::user()->firstname,
                    'lastname' => Auth::user()->lastname,
                ]),
                'subtitle' => __('notifications.representation.subtitle', [
                    'alias' => $representation->alias,
                ]),
                'user_id' => Auth::id(),
                'representation_id' => $representation->id,
                'type_id' => Notification::REPRESENTATION,
            ]);
        }

        return $this->sendResponse();
    }

    /**
     * Shows a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $guid
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request, $guid)
    {
        $representation = Representation::with("image")
            ->select(
                "representations.alias",
                "representations.firstname",
                "representations.lastname",
                "representations.document_number",
                "representations.country_id",
                "representations.province_id",
                "representations.address",
                "representations.city",
                "representations.cp",
                "representations.representation_type_id",
                "representations.status",
                "representations.archive_id",
            )
            ->where("guid", $guid)
            ->where("user_id", Auth::id())
            ->first();

        if (!$representation)
        {
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }

        $this->response = RepresentationResource::make($representation);
        $this->total = 1;
        $this->code = ResponseAlias::HTTP_OK;

        return $this->sendResponse();
    }

    public function edit(Request $request, $guid)
    {
        $representation = Representation::where('guid', $guid)
            ->where('user_id', Auth::id())
            ->first();

        if (!$representation)
        {
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }
        else if ($representation->status != 2)
        {
            $this->messages[] = 'Cannot edit a representation that is being validated or has already been validated';
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
            return $this->sendResponse();
        }

        $validator = Validator::make($request->all(), [
            'alias' => 'required|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'file' => 'nullable|mimes:jpg,jpeg,png,pdf',
            'document_number' => ['required', new Dni()],
            'address' => 'required|string',
            'city' => 'required|string',
            'province_id' => 'required',
            'country_id' => 'required',
            'cp' => 'required|string',
            'representation_type_id' => 'required',
        ]);

        if ($validator->fails())
        {
            $this->messages[] = $validator->errors()->messages();
            $this->code = ResponseAlias::HTTP_UNAUTHORIZED;
        }
        else
        {
            if ($request->hasFile("file")) {
                $file = Storage::disk("public")->put("", $request->file("file"));
                $archive = Archive::create([
                    "name" => $request->file("file")->getClientOriginalName(),
                    "path" => $file
                ]);
            }

            $values = array_merge(
                Arr::except($validator->validated(), 'file'),
                $request->hasFile("file") ? ['archive_id' => $archive->id] : [],
            );

            Representation::where('guid', $guid)
                ->where('user_id', Auth::id())
                ->update($values);

            $this->code = ResponseAlias::HTTP_OK;
        }

        return $this->sendResponse();
    }

    public function delete(Request $request, $guid)
    {
        $representation = Representation::where('guid', $guid)
            ->where('user_id', Auth::id())
            ->where('status', 0)
            ->first();

        if (!$representation)
        {
            $this->code = ResponseAlias::HTTP_NOT_FOUND;
            return $this->sendResponse();
        }

        $archive= Archive::find($representation->archive_id);
        Storage::disk("public")->delete($archive->path);
        $representation->delete();
        $archive->delete();
        $this->code = ResponseAlias::HTTP_OK;
        return $this->sendResponse();
    }
}
