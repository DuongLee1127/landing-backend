<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Http\Controllers\ImageController;

class AuthController extends Controller
{

    public $imageController;
    public function __construct(ImageController $imageController)
    {
        $this->imageController = $imageController;
    }
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
            $user->is_online = true;
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

    public function sendOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ], [
                'email.required' => "Email không được bỏ trống",
                'email.email' => "Không đúng định dạng email",
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
                'errors' => $e->errors(),
            ], 401);
        }

        $otpCode = rand(100000, 999999);

        Otp::where('email', $request->email)->delete();

        Otp::create([
            'email' => $request->email,
            'otp_code' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);
        // return "abc";
        try {
            Mail::to($request->email)->send(new OtpMail($otpCode));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gửi email thất bại. Vui lòng kiểm tra cấu hình máy chủ email.',
                'errors' => $e->getMessage(),
            ], 500);
        }
        // Mail::to($request->email)->send(new OtpMail($otpCode));

        return response()->json(['message' => 'OTP already send to you email']);
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
                    'otp' => 'required|digits:6',
                    'repassword' => 'required'
                ],
                [
                    'email.required' => "Email không được bỏ trống",
                    'email.email' => "Không đúng định dạng email",
                    'password.required' => "Mật khẩu không được bỏ trống",
                    'password.min' => "Mật khẩu ít nhất 6 ký tự",
                    'repassword.required' => "Mật khẩu nhập lại không được bỏ trống",
                    'password.same' => "Nhập lại mật khẩu không trùng",
                    'otp.required' => "Bạn chưa nhập mã OTP",
                ]
            );

            $otp = Otp::where('email', $request->email)
                ->where('otp_code', $request->otp)
                ->first();

            if (!$otp) {
                return response()->json(['message' => 'Mã OTP không hợp lệ.'], 400);
            }

            $otp->delete();

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
        } catch (\Exception $ge) {
            return response()->json([
                'message' => $ge->getMessage(),
            ], 500);
        }
    }

    public function user(Request $request)
    {
        // return "abc";
        $user = $request->user();
        // return $user;
        $url = $this->imageController->getImageUser($user->id);
        if (!empty($url[0])) {
            $user->image = $url[0]->url;
        } else {
            $user->image = null;
        }
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        $request->user()->is_online = false;
        return response()->json([
            'message' => "Đăng xuất",
            'user' => $request->user()
        ]);
    }
}
