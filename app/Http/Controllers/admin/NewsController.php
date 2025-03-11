<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\News;
use App\Models\NewsCategory;
<<<<<<< HEAD:app/Http/Controllers/admin/NewsController.php
use App\Http\Controllers\Controller;
=======
use Illuminate\Support\Facades\Log;

>>>>>>> cd34bb2f470c0271b19be4ad3e101cf04e333419:app/Http/Controllers/NewsController.php
class NewsController extends Controller
{
    public function show($categoryId)
    {
        $category = NewsCategory::find($categoryId);
        if (!$category || $category->deleted == 1) {
            return response()->json(['message' => 'Danh mục không tồn tại hoặc đã bị ẩn'], 404);
        }

        $news = $category->news()->where('deleted', 0)->get();

        if ($news->isEmpty()) {
            return response()->json(['message' => 'Không có tin tức trong danh mục này hoặc tất cả đã bị ẩn'], 404);
        }

        return response()->json($news);
    }
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $News = News::select('id', 'title', 'image', 'content', 'status', 'author', 'position', 'slug', 'views', 'likes','shares','newsCategory')
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
            'message' => 'tin tức.',
            'data' => $News
        ], 200);
    }
    public function store(Request $request)
{
        // Kiểm tra và xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|string',
            'author' => 'required|string|max:255',
            'position' => 'nullable|integer',
            'deleted' => 'nullable|integer',
            'newsCategory' => 'required|exists:newscategories,id',
            'status' => 'required|boolean',
            'featured' => 'required|boolean',
        ]);
        $maxPosition =  News::max('position') ?? 0;
        $newPosition = $maxPosition + 1;
        $slug = generateUniqueSlug($request->title,News::class);

        // Tạo tin tức mới
        $news = News::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $validated['image'] ?? null,
            'slug' => $slug,
            'author' => $validated['author'],
            'position' => $request->position ?? $newPosition,
            'deleted' => $validated['deleted'] ?? 0,
            'newsCategory' => $validated['newsCategory'] ?? 0,
            'status' => $validated['status'],
            'featured' => $validated['featured'],
        ]);

        return response()->json(['message' => 'Tin tức đã được thêm thành công', 'data' => $news], 201);
    }
    public function update(Request $request, $id)
    {
        // Tìm tin tức theo id
        $news = News::find($id);
        if (!$news) {
            return response()->json(['message' => 'Tin tức không tồn tại'], 404);
        }

        // Kiểm tra và xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|string',
            'author' => 'required|string|max:255',
            'position' => 'nullable|integer',
            'deleted' => 'nullable|integer',
            'newsCategory' => 'required|exists:newscategories,id',
            'status' => 'required|boolean',
            'featured' => 'required|boolean',
        ]);
        $position = $request->has('position') ? $request->position : $news->position;
    $slug = $news->title !== $validated['title'] 
    ? generateUniqueSlug($validated['title'], News::class)  // Sử dụng hàm generateUniqueSlug với bảng tương ứng
    : $news->slug;
        // Cập nhật thông tin tin tức
        $news->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $validated['image'],
            'slug' => $slug,
            'author' => $validated['author'],
            'position' => $validated['position'] ?? 0,
            'deleted' => $validated['deleted'] ?? 0,
            'newsCategory' => $validated['newsCategory'] ?? 0,
            'status' => $validated['status'],
            'featured' => $validated['featured'],
        ]);

        return response()->json(['message' => 'Tin tức đã được cập nhật thành công', 'data' => $news]);
    }
    // Phục hồi tin tức
    public function restore($id)
    {
        $news = News::find($id);
        if (!$news) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        // Phục hồi tin tức
        $news->restoreNews();

        return response()->json(['message' => 'Bài viết đã được phục hồi']);
    }
    
    // Ẩn tin tức
    public function softDelete($id)
    {
        $news = News::find($id);
        if (!$news) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        // Đánh dấu bài viết là bị ẩn (deleted = 1)
        $news->deleted = 1;
        $news->save();

        return response()->json(['message' => 'Bài viết đã bị ẩn']);
    }
}
