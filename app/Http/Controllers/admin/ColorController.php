<?php

namespace App\Http\Controllers\admin;

use App\Models\Color;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $perPage = $request->input('per_page', 10);
        $colors = Color::select('id', 'name', 'status', 'position')
            ->where('deleted', false)
            ->orderBy('position', 'desc')
            // ->paginate($perPage);
            ->get();

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách màu sắc.',
            'data' => $colors
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|integer|in:0,1',
            'position' => 'nullable|integer'
        ]);

        // tự động tăng cho possiton
        $maxPosition = Color::max('position') ?? 0;
        $newPosition = $maxPosition + 1;

        $color = Color::create([
            'name' => $request->name,
            'status' => $request->status ?? 1,
            'position' => $request->position ?? $newPosition,
        ]);

        return response()->json([
            'code' => 'success',
            'message' => 'Thêm màu sắc thành công.',
            'data' => $color
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $color = Color::find($id);
        if (!$color) {
            return response()->json([
                'code' => 'error',
                'message' => 'Màu sắc không tồn tại.'
            ], 404);
        }
        return response()->json([
            'code' => 'success',
            'message' => 'chi tiết màu sắc.',
            'data' => $color->only(['id', 'name', 'status', 'position'])
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $color = Color::find($id);
        if (!$color) {
            return response()->json([
                'code' => 'error',
                'message' => 'Màu sắc không tồn tại.'
            ], 404);
        }

        $request->validate([
            'name' => 'string|max:255',
            'status' => 'integer|in:0,1',
            'position' => 'nullable|integer'
        ]);

        $color->fill($request->only(
            [
                'name',
                'status',
                'position'
            ]
        ));

          // Nếu có thay đổi, mới lưu
            if ($color->isDirty()) {
                $color->save();
            }

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật màu sắc thành công.',
            'data' => $color->only(['id', 'name', 'status', 'position'])
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $color = Color::find($id);
        if (!$color) {
            return response()->json([
                'code' => 'error',
                'message' => 'Màu sắc không tồn tại.'
            ], 404);
        }

        $color->delete();
        return response()->json([
            'code' => 'success',
            'message' => 'Xóa màu sắc thành công.',
        ], 200);
    }


    public function softDelete(string $id)
    {
        $color = Color::where('deleted', false)->find($id);
        if (!$color) {
            return response()->json([
                'code' => 'error',
                'message' => 'Màu sắc không tồn tại.'
            ], 404);
        }
    
        // Xóa mềm
        $color->update(['deleted' => true]);
    
        return response()->json([
            'code' => 'success',
            'message' => 'Xóa màu sắc thành công.',
        ], 200);
    }


    public function restore(string $id)
{
    $color = Color::where('deleted', true)->find($id);
        if (!$color) {
            return response()->json([
                'code' => 'error',
                'message' => 'Màu sắc không tồn tại.'
            ], 404);
        }

    // Khôi phục danh mục
    $color->update(['deleted' => false]);

    return response()->json([
        'code' => 'success',
        'message' => 'Khôi phục màu sắc thành công.',
    ], 200);
}

// list nopage

public function listColor()
{
    // $perPage = $request->input('per_page', 10);
    $colors = Color::select('id', 'name', 'status', 'position')
        ->where('deleted', false)
        ->orderBy('position', 'desc')
        // ->paginate($perPage);
        ->get();

    return response()->json([
        'code' => 'success',
        'message' => 'Danh sách màu sắc.',
        'data' => $colors
    ], 200);
}
}
