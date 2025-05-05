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
    public function index(Request $request)
    {
        // phân quyền
        $this->authorize('viewAny', Color::class);

        $perPage = $request->input('per_page', 10);
        $status = $request->input('status');
        $search = $request->input('search');
        $sort = $request->input('sort');

        $query = Color::select('id', 'name', 'status', 'position')
            ->where('deleted', false);

        // Lọc theo trạng thái
        if ($status === 'active') {
            $query->where('status', 1);
        } elseif ($status === 'inactive') {
            $query->where('status', 0);
        }

        // Tìm kiếm theo tên
        if (!empty($search)) {
            $query->where('name', 'like', "%$search%");
        }

        // Sắp xếp
        switch ($sort) {
            case 'position-asc':
                $query->orderBy('position', 'asc');
                break;
            case 'position-desc':
                $query->orderBy('position', 'desc');
                break;
            case 'title-asc':
                $query->orderBy('name', 'asc');
                break;
            case 'title-desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('position', 'desc');
                break;
        }

        $colors = $query->paginate($perPage);

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
        // phân quyền
        $this->authorize('create', Color::class);

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
        // phân quyền
        $this->authorize('view', $color);
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
        // phân quyền
        $this->authorize('update', $color);
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
        // phân quyền
        $this->authorize('forceDelete', $color);
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
        // phân quyền
        $this->authorize('delete', $color);
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
        // phân quyền
        $this->authorize('restore', $color);
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

    // cập nhật trạng thái
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $color = Color::find($id);
        // phân quyền
        $this->authorize('update', $color);
        if (!$color) {
            return response()->json([
                'code' => 'error',
                'message' => 'Màu sắc không tồn tại!'
            ], 404);
        }

        $color->status = $request->status;
        $color->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => $color
        ]);
    }

    // thay đổi vị trí
    public function updatePosition(Request $request, $id)
    {
        $request->validate([
            'position' => 'required|integer|min:1',
        ]);

        $color = Color::where('deleted', false)->find($id);
        // phân quyền
        $this->authorize('update', $color);
        if (!$color) {
            return response()->json([
                'code' => 'error',
                'message' => 'Màu sắc không tồn tại.',
            ], 404);
        }

        $color->position = $request->position;
        $color->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật vị trí thành công.',
            'data' => $color
        ]);
    }

    public function TrashColor(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $colors = Color::select('id', 'name', 'status', 'position')
            ->where('deleted', true)
            ->orderBy('position', 'desc')
            ->paginate($perPage);

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách màu sắc.',
            'data' => $colors
        ], 200);
    }
}
