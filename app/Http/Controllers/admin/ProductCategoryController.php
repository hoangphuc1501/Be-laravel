<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        // return response()->json(ProductCategory::all(), 200);
        $categories = ProductCategory::select('id', 'name', 'image', 'description', 'status', 'parentID', 'position', 'slug')
            ->where('deleted', false)
            ->orderBy('position', 'desc')
            ->paginate($perPage);
            // ->get();
        
        // lấy danh mục cha
        // $categories = ProductCategory::whereNull('parentID')
        // ->with('children') // Lấy luôn danh mục con
        // ->get(); 
        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách danh mục sản phẩm.',
            'data' => $categories
        ], 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // Kiểm tra dữ liệu hợp lệ trước khi lưu
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'parentID' => 'nullable|exists:productcategories,id', // Đảm bảo parentID tồn tại
            'position' => 'nullable|integer',
        ]);
        // tự động tăng cho possiton
        $maxPosition = ProductCategory::max('position') ?? 0;
        $newPosition = $maxPosition + 1;

        // Tạo slug
        // $slug = generateUniqueSlug($request->name);
        $slug = generateUniqueSlug($request->name, ProductCategory::class);
        // Tạo danh mục mới
        $category = ProductCategory::create([
            'name' => $request->name,
            'image' => $request->image,
            'description' => $request->description,
            'status' => $request->status,
            'parentID' => $request->parentID, // Nếu NULL thì là danh mục cha
            'position' => $request->position ?? $newPosition,
            'slug' => $slug,
            'deleted' => 0
        ]);

        // Trả về dữ liệu có quan hệ cha - con
        return response()->json([
            'code' => 'success',
            'message' => 'Thêm danh mục thành công.',
            'data' => $category->load('parent', 'children') // Load quan hệ để xem danh mục cha - con
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = ProductCategory::find($id);
        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục không tồn tại.'
            ], 404);
        }
        return response()->json([
            'code' => 'success',
            'message' => 'Hiển thị danh mục theo id thành công.',
            'data' => $category->only(['id', 'name', 'image', 'description', 'status', 'parentID', 'slug', 'deleted', 'position'])
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    $category = ProductCategory::find($id);
    if (!$category) {
        return response()->json([
            'code' => 'error',
            'message' => 'Danh mục không tồn tại.'
        ], 404);
    }

    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'image' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|boolean',
        'parentID' => 'nullable|exists:productcategories,id',
        'position' => 'nullable|integer',
    ]);

    // Kiểm tra nếu tên thay đổi thì tạo slug mới
    // $slug = $category->name !== $validatedData['name'] 
    //     ? generateUniqueSlug($validatedData['name']) 
    //     : $category->slug;

    $position = $request->has('position') ? $request->position : $category->position;
    $slug = $category->name !== $validatedData['name'] 
    ? generateUniqueSlug($validatedData['name'], ProductCategory::class)  // Sử dụng hàm generateUniqueSlug với bảng tương ứng
    : $category->slug;

     // Cập nhật parentID, có thể null
    $parentID = $request->has('parentID') ? $request->input('parentID') : $category->parentID;

    $category->fill([
        'name' => $validatedData['name'],
        'image' => $validatedData['image'] ?? $category->image,
        'description' => $validatedData['description'] ?? $category->description,
        'status' => $validatedData['status'],
        'parentID' => $parentID,
        'position' => $position,
        'slug' => $slug,
    ]);

    // Kiểm tra nếu dữ liệu thay đổi mới update
    if ($category->isDirty()) {
        $category->save();
    }

    return response()->json([
        'code' => 'success',
        'message' => 'Cập nhật danh mục thành công.',
        'data' => $category->only(['id', 'name', 'image', 'description', 'status', 'parentID', 'position', 'slug'])
    ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = ProductCategory::find($id);
        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục không tồn tại.'
            ], 404);
        }

        $category->delete();
        return response()->json(['message' => 'Xóa thành công'], 200);

    }
    

    public function softDelete(string $id)
{
    $category = ProductCategory::where('deleted', false)->find($id);

    if (!$category) {
        return response()->json([
            'code' => 'error',
            'message' => 'Danh mục không tồn tại.'
        ], 404);
    }

    // Xóa mềm
    $category->update(['deleted' => true]);

    return response()->json([
        'code' => 'success',
        'message' => 'Xóa danh mục thành công.',
    ], 200);
}

public function restore(string $id)
{
    $category = ProductCategory::where('deleted', true)->find($id);

    if (!$category) {
        return response()->json([
            'code' => 'error',
            'message' => 'Danh mục không tồn tại.'
        ], 404);
    }

    // Khôi phục danh mục
    $category->update(['deleted' => false]);

    return response()->json([
        'code' => 'success',
        'message' => 'Khôi phục danh mục thành công.',
    ], 200);
}

// no page
public function ListCategory(Request $request)
{
    $categoriesList = ProductCategory::select('id', 'name')
        ->where('deleted', 0)
        ->where('status', 1)
        ->orderBy('position', 'asc')
        ->get();

    if ($categoriesList->isEmpty()) {
        return response()->json([
            'code' => 'error',
            'message' => 'Không có danh mục nào.',
            'data' => []
        ], 404);
    }

    return response()->json([
        'code' => 'success',
        'message' => 'Danh sách danh mục sản phẩm.',
        'data' => $categoriesList
    ], 200);
}
}
