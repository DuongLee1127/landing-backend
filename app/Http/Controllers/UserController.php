<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    //
    function getUserIDInfo($id)
    {
        $user = DB::table('users')->where('id', $id)->select('name', 'email')->first();
        $url = $this->getImageUser($id);
        if (!empty($url[0])) {
            $user->image = $url[0]->url;
        } else {
            $user->image = null;
        }
        return response()->json($user);
    }

    function updateUser(Request $request, $id)
    {
        try {
            $validate = $request->validate(
                [
                    'email' => 'required|email',
                    'name' => 'required',
                    'image' => 'required',
                ],
                [
                    'email.required' => "Email không được bỏ trống",
                    'email.email' => "Không đúng định dạng email",
                    'name.required' => "Tên không được bỏ trống",
                    'image.required' => "Bạn chưa chọn ảnh",
                ]
            );

            if (is_string($request->image)) {
                $url = $request->image;
                $path = explode('/storage/', parse_url($url, PHP_URL_PATH))[1];

            } else {
                $request->validate(
                    [
                        'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                    ],
                    [
                        'image.image' => "Trường hình ảnh phải là một hình ảnh",
                        'image.mimes' => "Hình ảnh phải có định dạng: jpeg, png, jpg, gif, webp",
                    ]
                );
                $path = $request->file('image')->store('uploads', 'public');
                $url = asset('storage/' . $path);
            }
            $user = User::find($id);
            if ($user->email != $request->email) {
                $user->update(
                    [
                        'name' => $request->name,
                        'email' => $request->email,
                    ]
                );
            } else {
                $user->update(
                    [
                        'name' => $request->name,
                    ]
                );
            }

            if (!$user) {
                return response()->json([
                    'message' => "Cập nhật thông tin User thất bại"
                ], 404);
            }

            $image = Image::updateOrCreate(
                ['user_id' => $id],
                ['path' => $path, 'url' => $url]
            );
            if (!$image) {
                return response()->json([
                    'message' => "Upload ảnh thất bại",
                    'image' => $image
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
        } catch (QueryException $q) {
            return response()->json([
                'query message' => $q->getMessage(),
                'query error' => $q->getMessage(),
            ], 401);
        }
    }
}
