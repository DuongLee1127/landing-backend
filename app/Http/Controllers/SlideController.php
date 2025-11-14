<?php

namespace App\Http\Controllers;

use App\Models\Slide;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SlideController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif',
            ], [
                'image.required' => "Bạn chưa chọn ảnh",
                'image.image' => "Trường hình ảnh phải là một hình ảnh",
                'image.mimes' => "Hình ảnh phải có định dạng: jpeg, png, jpg, gif",
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        $path = $request->file('image')->store('uploads', 'public');
        $url = asset('storage/' . $path);
        $slide = Slide::create(['path' => $path, 'url' => $url, 'user_id' => $request->user()->id]);

        if (!$slide) {
            return response()->json(['error' => 'Tạo slide không thành công'], 500);
        }

        return response()->json(['message' => 'Thêm mới slide thành công', 'url' => $url, 'user_name' => $request->user()->name], 200);
    }
    public function show()
    {
        $slides = DB::table('slides')->select('*')->get();

        $slides = $slides->reverse()->values()->all();

        foreach ($slides as $slide) {
            $user = User::find($slide->user_id);
            $slide->user_name = $user->name;
        }
        return response()->json($slides, 200);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $slide = Slide::find($id);
        if (!$slide) {
            return response()->json(['error' => 'Slide không tồn tại'], 404);
        }

        $slide->delete();
        return response()->json(['message' => 'Xóa slide thành công'], 200);
    }
}
