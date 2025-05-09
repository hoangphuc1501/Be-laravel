<?php

namespace App\Http\Controllers\admin;
use App\Models\Brands;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    { 

        $this->authorize('viewAny', Brands::class);
        $perPage = $request->input('per_page', 10);
        $status = $request->input('status');
        $search = $request->input('search');
        $sort = $request->input('sort');

        $query = Brands::select('id', 'name', 'image', 'description', 'status', 'position', 'slug')
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

        $brands = $query->paginate($perPage);

        return response()->json([
            'code' => 'success',
            'message' => "Hiển thị danh sách thương hiệu thành công",
            'data' => $brands,
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Brands::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'position' => 'nullable|integer',
        ]);

        // Tự động tăng cho position
        $maxPosition = Brands::max('position') ?? 0;
        $newPosition = $maxPosition + 1;

        // Tạo slug cho tên brand
        $slug = generateUniqueSlug($request->name, Brands::class);

        // Tạo brand mới
        $brand = Brands::create([
            'name' => $request->name,
            'image' => $request->image,
            'description' => $request->description,
            'status' => $request->status,
            'position' => $request->position ?? $newPosition,
            'slug' => $slug,
            'deleted' => 0
        ]);
        // Trả về kết quả
        return response()->json([
            'code' => 'success',
            'message' => 'Thêm thương hiệu thành công.',
            'data' => $brand
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brands::find($id);
        // phân quyền
        $this->authorize('view', $brand);
        if (!$brand) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thương hiệu không tồn tại!'
            ], 400);
        }

        // Trả về thông tin thương hiệu
        return response()->json([
            'code' => 'success',
            'message' => 'Hiển thị thương hiệu theo id thành công.',
            'data' => $brand->only(['id', 'name', 'image', 'description', 'status', 'slug', 'deleted', 'position'])
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Kiểm tra brand có tồn tại không
        $brand = Brands::find($id);
        // phân quyền
        $this->authorize('update', $brand);
        if (!$brand) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thương hiệu không tồn tại.'
            ], 404);
        }

        // Xác thực dữ liệu đầu vào
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'position' => 'nullable|integer',
        ]);

        $position = $request->has('position') ? $validatedData['position'] : $brand->position;

        // Cập nhật slug nếu tên thay đổi, giữ nguyên nếu không đổi
        $slug = $brand->name !== $validatedData['name']
            ? generateUniqueSlug($validatedData['name'], Brands::class)
            : $brand->slug;

        // Gán dữ liệu mới vào model
        $brand->fill([
            'name' => $validatedData['name'],
            'image' => $validatedData['image'] ?? $brand->image,
            'description' => $validatedData['description'] ?? $brand->description,
            'status' => $validatedData['status'],
            'position' => $position,
            'slug' => $slug
        ]);

        // Kiểm tra nếu có thay đổi thì mới lưu vào database
        if ($brand->isDirty()) {
            $brand->save();
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật thương hiệu thành công.',
            'data' => $brand->only(['id', 'name', 'image', 'description', 'status', 'position', 'slug'])
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brands::find($id);
        // phân quyền
        $this->authorize('forceDelete', $brand);
        if (!$brand) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thương hiệu không tồn tại!'
            ], 400);
        }

        $brand->delete();
        return response()->json([
            'code' => 'success',
            'message' => 'Xóa thương hiệu thành công.',
        ], 200);
    }

    public function softDelete(string $id)
    {
        $brand = Brands::where('deleted', false)->find($id);
        // phân quyền
        $this->authorize('delete', $brand);
        if (!$brand) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thương hiệu không tồn tại hoặc đã bị xóa!'
            ], 400);
        }

        // Xóa mềm
        $brand->update(['deleted' => true]);

        return response()->json([
            'code' => 'success',
            'message' => 'Xóa Thương hiệu thành công.',
        ], 200);
    }

    public function restore(string $id)
    {
        $brand = Brands::where('deleted', true)->find($id);
        // phân quyền
        $this->authorize('restore', $brand);
        if (!$brand) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thương hiệu không tồn tại hoặc chưa bị xóa!'
            ], 400);
        }

        // Khôi phục danh mục
        $brand->update(['deleted' => false]);

        return response()->json([
            'code' => 'success',
            'message' => 'Khôi phục thương hiệu thành công.',
        ], 200);
    }

    // nopage
    public function ListBrands(Request $request)
    {
        $BrandList = Brands::select('id', 'name')
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderBy('position', 'asc')
            ->get();

        if ($BrandList->isEmpty()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không có thương hiệu nào.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách thương hiệu sản phẩm.',
            'data' => $BrandList
        ], 200);
    }

    // cập nhật trạng thái
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $brand = Brands::find($id);
        // phân quyền
        $this->authorize('update', $brand);
        if (!$brand) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thương hiệu không tồn tại!'
            ], 404);
        }

        $brand->status = $request->status;
        $brand->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => $brand
        ]);
    }

    // thay đổi vị trí
    public function updatePosition(Request $request, $id)
    {
        $request->validate([
            'position' => 'required|integer|min:1',
        ]);

        $brand = Brands::where('deleted', false)->find($id);
        $this->authorize('update', $brand);
        if (!$brand) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thương hiệu không tồn tại!'
            ], 404);
        }

        $brand->position = $request->position;
        $brand->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật vị trí thành công.',
            'data' => $brand
        ]);
    }

    // thùng rac
    public function trashBrand(Request $request)
{
    $perPage = $request->input('per_page', 10);

    $brands = Brands::select('id', 'name', 'image', 'description', 'status', 'position', 'slug')
        ->where('deleted', true)
        ->orderBy('position', 'desc')
        ->paginate($perPage);

    return response()->json([
        'code' => 'success',
        'message' => "Hiển thị danh sách thương hiệu thành công",
        'data' => $brands,
    ], 200);
}

}
