<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'message' => 'Danh sách danh mục sản phẩm.',
            'data' => $categoriesNews
        ], 200);
    }
    public function getNewsByCategory($categoryId)
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

    // Lấy tất cả tin tức
    public function getAllNews()
    {
        $news = News::notDeleted()->get();
        return response()->json($news);
    }

    // Ẩn danh mục và các tin tức liên quan
    public function deleteCategory($id)
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

    // Ẩn tin tức
    public function deleteNews($id)
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

    // Phục hồi danh mục và các tin tức liên quan
    public function restoreCategory($newsCategory)
    {
        $category = NewsCategory::find($newsCategory);
        $category->restoreCategory();

        $category->news()->where('deleted', 1)->update(['deleted' => 0]);

        return response()->json(['message' => 'Danh mục và các bài viết liên quan đã được phục hồi']);
    }

    // Phục hồi tin tức
    public function restoreNews($id)
    {
        $news = News::find($id);
        if (!$news) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        // Phục hồi tin tức
        $news->restoreNews();

        return response()->json(['message' => 'Bài viết đã được phục hồi']);
    }
    public function store(Request $request)
    {
        // Kiểm tra và xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|string',
            'slug' => 'required|string|unique:news',
            'author' => 'required|string|max:255',
            'position' => 'nullable|integer',
            'deleted' => 'nullable|integer',
            'newsCategory' => 'required|exists:newscategories,id',
            'status' => 'required|boolean',
            'featured' => 'required|boolean',
        ]);

        // Tạo tin tức mới
        $news = News::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $validated['image'],
            'slug' => Str::slug($validated['slug']),
            'author' => $validated['author'],
            'position' => $validated['position'] ?? 0,
            'deleted' => $validated['deleted'] ?? 0,
            'newsCategory' => $validated['newsCategory'],
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
            'slug' => 'required|string|unique:news,slug,' . $news->id,
            'author' => 'required|string|max:255',
            'position' => 'nullable|integer',
            'deleted' => 'nullable|integer',
            'newsCategory' => 'required|exists:newscategories,id',
            'status' => 'required|boolean',
            'featured' => 'required|boolean',
        ]);

        // Cập nhật thông tin tin tức
        $news->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $validated['image'],
            'slug' => Str::slug($validated['slug']),
            'author' => $validated['author'],
            'position' => $validated['position'] ?? 0,
            'deleted' => $validated['deleted'] ?? 0,
            'newsCategory' => $validated['newsCategory'],
            'status' => $validated['status'],
            'featured' => $validated['featured'],
        ]);

        return response()->json(['message' => 'Tin tức đã được cập nhật thành công', 'data' => $news]);
    }
}
