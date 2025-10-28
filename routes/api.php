<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Storage;
use App\Models\Image;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/upload', function (Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = $request->file('image')->store('uploads', 'public');
        $url = asset('storage/' . $path);



        $image = Image::create([
            'user_id' => $request->user()->id,
            'path' => $path,
            'url' => $url,
        ]);

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
});
