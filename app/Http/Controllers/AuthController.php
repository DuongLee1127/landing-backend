<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use function PHPUnit\Framework\isEmpty;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validate = $request->validate(
                [
                    'email' => 'required|email',
                    'password' => 'required|min:6|max:16',
                ],
                [
                    'email.required' => "Email không được bỏ trống",
                    'email.email' => "Không đúng định dạng email",
                    'password.required' => "Mật khẩu không được bỏ trống",
                    'password.min' => "Mật khẩu ít nhất 6 ký tự"
                ]
            );

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password, [])) {
                return response()->json([
                    'message' => "Lỗi đăng nhập"
                ], 401);
            }

            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'message' => 'Đăng nhập thành công',
                'token' => $token,
                'user' => $user
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
                'errors' => $e->errors(),
            ], 401);
        }


    }

    public function register(Request $request)
    {
        // return [
        //     'email' => $request->email,
        //     'name' => $request->name,
        //     'password' => $request->password
        // ];

        try {
            $validate = $request->validate(
                [
                    'email' => 'required|email',
                    'password' => 'required|min:6|max:16|same:repassword',
                    'repassword' => 'required'
                ],
                [
                    'email.required' => "Email không được bỏ trống",
                    'email.email' => "Không đúng định dạng email",
                    'password.required' => "Mật khẩu không được bỏ trống",
                    'password.min' => "Mật khẩu ít nhất 6 ký tự",
                    'repassword.required' => "Mật khẩu nhập lại không được bỏ trống",
                    'password.same' => "Nhập lại mật khẩu không trùng",
                ]
            );

            User::create([
                'email' => $request->email,
                'name' => $request->name,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'message' => 'Đăng ký tài khoản thành công'
            ], 200);


        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
                'errors' => $e->errors(),
            ], 401);
        }



    }

    public function user(Request $request)
    {
        $user = $request->user();
        $user->image = $this->getImageUser($user->id)[0]->url;
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => "Đăng xuất"
        ]);
    }
}
