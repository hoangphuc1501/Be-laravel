<?php

namespace App\Http\Controllers\Admin;

use App\Models\Size;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SizeController extends Controller
{
    public function index(Request $request)
{
    $perPage = $request->input('per_page', 10);
    $status = $request->input('status');
    $search = $request->input('search');
    $sort = $request->input('sort');

    $query = Size::select('id', 'name', 'status', 'position')
        ->where('deleted', false);

    // Lọc theo trạng thái
    if ($status === 'active') {
        $query->where('status', 1);
    } elseif ($status === 'inactive') {
        $query->where('status', 0);
    }

    // Tìm kiếm theo tên
    if (!empty($search)) {
        $query->where('name', 'like', '%' . $search . '%');
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

    $sizes = $query->paginate($perPage);

    return response()->json([
        'code' => 'success',
        'message' => 'Danh sách kích thước.',
        'data' => $sizes
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
        $maxPosition = Size::max('position') ?? 0;
        $newPosition = $maxPosition + 1;

        $size = Size::create([
            'name' => $request->name,
            'status' => $request->status ?? 1,
            'position' => $request->position ?? $newPosition,
        ]);

        return response()->json([
            'code' => 'success',
            'message' => 'Thêm kích thước thành công.',
            'data' => $size
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $size = Size::find($id);
        if (!$size) {
            return response()->json([
                'code' => 'error',
                'message' => 'Kích thước không tồn tại.'
            ], 404);
        }
        return response()->json([
            'code' => 'success',
            'message' => 'chi tiết kích thước.',
            'data' => $size->only(['id', 'name', 'status', 'position'])
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $size = Size::find($id);
        if (!$size) {
            return response()->json([
                'code' => 'error',
                'message' => 'Kích thước không tồn tại.'
            ], 404);
        }

        $request->validate([
            'name' => 'string|max:255',
            'status' => 'integer|in:0,1',
            'position' => 'nullable|integer'
        ]);

        $size->fill($request->only(
            [
                'name',
                'status',
                'position'
            ]
        ));

          // Nếu có thay đổi, mới lưu
            if ($size->isDirty()) {
                $size->save();
            }

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật Kích thước thành công.',
            'data' => $size->only(['id', 'name', 'status', 'position'])
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $size = Size::find($id);
        if (!$size) {
            return response()->json([
                'code' => 'error',
                'message' => 'Kích thước không tồn tại.'
            ], 404);
        }

        $size->delete();
        return response()->json([
            'code' => 'success',
            'message' => 'Xóa kích thước thành công.',
        ], 200);
    }


    public function softDelete(string $id)
    {
        $size = Size::where('deleted', false)->find($id);
        if (!$size) {
            return response()->json([
                'code' => 'error',
                'message' => 'Kích thước không tồn tại.'
            ], 404);
        }
    
        // Xóa mềm
        $size->update(['deleted' => true]);
    
        return response()->json([
            'code' => 'success',
            'message' => 'Xóa kích thước thành công.',
        ], 200);
    }


    public function restore(string $id)
{
    $size = Size::where('deleted', true)->find($id);
        if (!$size) {
            return response()->json([
                'code' => 'error',
                'message' => 'Kích thước không tồn tại.'
            ], 404);
        }

    // Khôi phục danh mục
    $size->update(['deleted' => false]);

    return response()->json([
        'code' => 'success',
        'message' => 'Khôi phục kích thước thành công.',
    ], 200);
}


public function listSize()
    {
        // $perPage = $request->input('per_page', 10);
        $sizes = Size::select('id', 'name', 'status', 'position')
            ->where('deleted', false)
            ->orderBy('position', 'desc')
            // ->paginate($perPage);
            ->get();

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách kích thước.',
            'data' => $sizes
        ], 200);
    }

    // cập nhật trạng thái
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $size = Size::find($id);
        if (!$size) {
            return response()->json([
                'code' => 'error',
                'message' => 'Kích thước không tồn tại!'
            ], 404);
        }

        $size->status = $request->status;
        $size->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => $size
        ]);
    }

    // thay đổi vị trí
    public function updatePosition(Request $request, $id)
    {
        $request->validate([
            'position' => 'required|integer|min:1',
        ]);

        $size = Size::where('deleted', false)->find($id);

        if (!$size) {
            return response()->json([
                'code' => 'error',
                'message' => 'Kích thước không tồn tại.',
            ], 404);
        }

        $size->position = $request->position;
        $size->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật vị trí thành công.',
            'data' => $size
        ]);
    }

    // 
    public function TrashSize(Request $request)
{
    $perPage = $request->input('per_page', 10);

    $sizes = Size::select('id', 'name', 'status', 'position')
        ->where('deleted', true)
        ->orderBy('position', 'desc')
        ->paginate($perPage);

    return response()->json([
        'code' => 'success',
        'message' => 'Danh sách kích thước.',
        'data' => $sizes
    ], 200);
}
}
