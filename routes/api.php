<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SlideController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Image;
use App\Http\Middleware\CheckRole;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/get-token', [Controller::class, 'getToken']);
Route::middleware(['auth:sanctum', CheckRole::class])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout'])->withoutMiddleware([CheckRole::class]);
    Route::post('/upload', function (Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'image.required' => "Bạn chưa chọn ảnh",
            'image.image' => "Trường hình ảnh phải là một hình ảnh",
            'image.mimes' => "Hình ảnh phải có định dạng: jpeg, png, jpg, gif",
        ]);

        $path = $request->file('image')->store('uploads', 'public');
        $url = asset('storage/' . $path);

        $image = DB::table('images')
            ->updateOrInsert(
                ['user_id' => $request->user()->id],
                ['path' => $path, 'url' => $url]
            );

        return response()->json([
            'message' => 'Upload thành công!',
            'image' => $image,
        ]);
    });
    Route::get('/images', function (Request $request) {
        $images = Image::where('user_id', $request->user()->id)->latest()->get();

        return response()->json($images);
    });
    Route::delete('/image/{id}', function ($id, Request $request) {
        $image = Image::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(['message' => 'Đã xoá ảnh!']);
    });
    Route::get('/get-users', [UserController::class, 'getUserInfo']);
    // Route::get('/get-images/{id}', [Controller::class, 'getImageUser']);
    Route::get('get-user/{id}', [UserController::class, 'getUserIDInfo']);
    Route::post('update-user/{id}', [UserController::class, 'updateUser']);
    Route::post('/add-slide', [SlideController::class, 'store']);
    Route::get('/slides', [SlideController::class, 'show']);
    Route::delete('/delete/{id}', [SlideController::class, 'destroy']);
});
