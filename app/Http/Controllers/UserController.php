<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    //
    function getUserIDInfo($id)
    {
        $user = DB::table('users')->where('id', $id)->select('name', 'email')->first();
        $url = $this->getImageUser($id);
        $user->image = $url[0]->url;
        return response()->json($user);
    }

    function updateUser(Request $request, $id)
    {
        try {
            $validate = $request->validate(
                [
                    'email' => 'required|email',
                    'name' => 'required',
                    'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                ],
                [
                    'email.required' => "Email không được bỏ trống",
                    'email.email' => "Không đúng định dạng email",
                    'name.required' => "Tên không được bỏ trống",
                    'image.required' => "Bạn chưa chọn ảnh",
                    'image.image' => "Trường hình ảnh phải là một hình ảnh",
                    'image.mimes' => "Hình ảnh phải có định dạng: jpeg, png, jpg, gif, webp",
                ]
            );

            $updateUser = DB::table('users')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);
            if (!$updateUser) {
                return response()->json([
                    'message' => "Cập nhật thông tin thất bại"
                ], 404);
            }

            $path = $request->file('image')->store('uploads', 'public');
            $url = asset('storage/' . $path);

            $image = DB::table('images')
                ->updateOrInsert(
                    ['user_id' => $id],
                    ['path' => $path, 'url' => $url]
                );
            if (!$image) {
                return response()->json([
                    'message' => "Upload ảnh thất bại"
                ], 404);
            }

            return response()->json([
                'message' => "Update thành công",
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
                'errors' => $e->errors(),
            ], 401);
        }
    }
}
