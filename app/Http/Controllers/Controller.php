<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Image;
use Illuminate\Support\Facades\DB;

class Controller
{
    //
    function getUserInfo()
    {
        $users = DB::table('users')->select('name', 'email', 'id')->get();
        $data = [
        ];

        foreach ($users as $user) {
            $url = $this->getImageUser($user->id);
            $user->image = $url[0]->url;
            $data[] = $user;
        }
        return response()->json($data);
    }

    function getImageUser($id)
    {
        $url = DB::table('images')
            ->where('images.user_id', $id)
            ->select('url')
            ->get();
        return $url;
    }


}
