<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\NewsCategory;
use Illuminate\Support\Facades\Log;

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'parentID' => 'nullable|exists:newscategories,id',
            'position' => 'nullable|integer',
        ]);

        // Tự động tăng cho position
        $maxPosition = NewsCategory::max('position') ?? 0;
        $newPosition = $maxPosition + 1;

        // Tạo slug
        $slug = generateUniqueSlug($validated['name'], NewsCategory::class);

        // Tạo danh mục mới
        $category = NewsCategory::create([
            'name' => $validated['name'],
            'image' => $validated['image'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'parentID' => $validated['parentID'] ?? null,
            'position' => $validated['position'] ?? $newPosition,
            'slug' => $slug,
            'deleted' => 0
        ]);


        return response()->json([
            'code' => 'success',
            'message' => 'Thêm danh mục thành công.',
            'data' => $category->load('parent', 'children')
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
    public function update(Request $request, $id)
    {
        // Tìm tin tức theo id
        $category = NewsCategory::find($id);
        if (!$category) {
            return response()->json(['message' => 'Tin tức không tồn tại'], 404);
        }

        // Kiểm tra và xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'position' => 'nullable|integer',
            'ParentID' => 'nullable|exists:newscategories,id',
            'status' => 'required|boolean',
        ]);
        $position = $request->has('position') ? $request->position : $category->position;
    $slug = $category->title !== $validated['name'] 
    ? generateUniqueSlug($validated['name'], NewsCategory::class)  // Sử dụng hàm generateUniqueSlug với bảng tương ứng
    : $category->slug;
        // Cập nhật thông tin tin tức
        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'image' => $validated['image'] ?? ($category->image ?? ''),
            'parentID' => $validated['parentID'] ?? 0,
            'slug' => $slug,
            'position' => $validated['position'] ?? 0,
            'status' => $validated['status'],
        ]);

        return response()->json(['message' => 'Tin tức đã được cập nhật thành công', 'data' => $category]);
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
