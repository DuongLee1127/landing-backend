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

        // return response()->json($users);
        $data = [
        ];

        foreach ($users as $user) {
            $url = $this->getImageUser($user->id);
            if (!empty($url[0])) {
                $user->image = $url[0]->url;
            } else {
                $user->image = null;
            }
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
