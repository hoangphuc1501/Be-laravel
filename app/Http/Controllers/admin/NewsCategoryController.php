<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\NewsCategory;
use App\Http\Controllers\Controller;
class NewsCategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $categoriesNews = NewsCategory::select('id', 'name', 'image', 'description', 'status', 'parentID', 'position', 'slug')
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
            'message' => 'Danh sách danh mục tin tức.',
            'data' => $categoriesNews
        ], 200);
    }
    

    public function store(Request $request)
    {

        // Kiểm tra dữ liệu hợp lệ trước khi lưu
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'parentID' => 'nullable|exists:newscategories,id', // Đảm bảo parentID tồn tại
            'position' => 'nullable|integer',
        ]);
        // tự động tăng cho possiton
        $maxPosition =  NewsCategory::max('position') ?? 0;
        $newPosition = $maxPosition + 1;

        // Tạo slug
        // $slug = generateUniqueSlug($request->name);
        $slug = generateUniqueSlug($request->name,  NewsCategory::class);
        // Tạo danh mục mới
        $category =  NewsCategory::create([
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
    public function show(string $id)
    {
        $category = NewsCategory::find($id);
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
    public function update(Request $request, string $id)
    {
    $category = NewsCategory::find($id);
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
        'parentID' => 'nullable|exists:newscategories,id',
        'position' => 'nullable|integer',
    ]);

    // Kiểm tra nếu tên thay đổi thì tạo slug mới
    // $slug = $category->name !== $validatedData['name'] 
    //     ? generateUniqueSlug($validatedData['name']) 
    //     : $category->slug;

    $position = $request->has('position') ? $request->position : $category->position;
    $slug = $category->name !== $validatedData['name'] 
    ? generateUniqueSlug($validatedData['name'], NewsCategory::class)  // Sử dụng hàm generateUniqueSlug với bảng tương ứng
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
    

    // Ẩn danh mục và các tin tức liên quan
    public function softDelete($id)
    {
        $category = NewsCategory::find($id);
        if (!$category) {
            return response()->json(['message' => 'Danh mục không tồn tại'], 404);
        }

        // Đánh dấu danh mục là bị ẩn (deleted = 1)
        $category->deleted = 1;
        $category->save();

        // Cập nhật tin tức liên quan
        $category->news()->update(['deleted' => 1]);

        return response()->json(['message' => 'Danh mục đã bị ẩn và các bài viết liên quan cũng đã bị ẩn']);
    }

    // Phục hồi danh mục và các tin tức liên quan
    public function restore($newsCategory)
    {
        $category = NewsCategory::find($newsCategory);
        $category->restore();

        $category->news()->where('deleted', 1)->update(['deleted' => 0]);

        return response()->json(['message' => 'Danh mục và các bài viết liên quan đã được phục hồi']);
    }
}
