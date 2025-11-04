<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Image;
use Illuminate\Support\Facades\DB;

class ImageController extends Controller
{
    //

    function getImageUser($id)
    {
        $url = DB::table('images')
            ->where('images.user_id', $id)
            ->select('url')
            ->get();
        return $url;
    }



}
