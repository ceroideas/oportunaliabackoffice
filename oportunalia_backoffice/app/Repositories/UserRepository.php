<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserRepository implements UserRepositoryInterface
{

    public function delete($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->email = uniqid('delete_') . "@delete.com";
            $user->firstname = uniqid('delete_');
            $user->lastname = uniqid('delete_');
            $user->username = uniqid('delete_');
            $user->phone = 1234;
            $user->address = "";
            $user->document_number = uniqid();
            $user->confirmed = 0;
            $user->status = 0;
            $user->save();
            Storage::disk("public")->delete($user->document_path);
            $user->delete();
        }
    }
}
