<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\News;
use App\Models\NewsCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
class NewsController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $status = $request->input('status');
        $search = $request->input('search');
        $sort = $request->input('sort');
        $featured = $request->input('featured');
        $categoryId = $request->input('categoryId');

        $query = News::with('category')
            ->where('deleted', false)
            ->orderBy('position', 'desc');

        // Lọc trạng thái
        if ($status === 'active') {
            $query->where('status', 1);
        } elseif ($status === 'inactive') {
            $query->where('status', 0);
        }
        if ($featured === 'yes') {
            $query->where('featured', 1);
        } elseif ($featured === 'no') {
            $query->where('featured', 0);
        }
        // Tìm kiếm theo tiêu đề
        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }


        if (!empty($categoryId)) {
            $query->where('newsCategory', $categoryId);
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
                $query->orderBy('title', 'asc');
                break;
            case 'title-desc':
                $query->orderBy('title', 'desc');
                break;
            default:
                $query->orderBy('position', 'desc');
                break;
        }

        $newsList = $query->paginate($perPage);

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách tin tức.',
            'data' => $newsList
        ]);
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

        $maxPosition = News::max('position') ?? 0;
        $newPosition = $maxPosition + 1;
        $slug = generateUniqueSlug($request->title, News::class);

        // Tạo tin tức mới
        $news = News::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $validated['image'] ?? null,
            'slug' => $slug,
            'author' => $validated['author'],
            'position' => $request->position ?? $newPosition,
            'newsCategory' => $validated['newsCategory'] ?? 0,
            'status' => $validated['status'],
            'featured' => $validated['featured'],
            'deleted' => false,
            'Views' => 0,
            'Likes' => 0,
            'Shares' => 0
        ]);

        return response()->json([
            'code' => 'success',
            'message' => 'Tạo tin tức thành công.',
            'data' => $news
        ]);
    }
    public function show($id)
    {
        $news = News::with('category')->find($id);

        if (!$news || $news->deleted) {
            return response()->json([
                'code' => 'error',
                'message' => 'Tin tức không tồn tại.'
            ], 404);
        }
        return response()->json([
            'code' => 'success',
            'message' => 'Chi tiết tin tức.',
            'data' => $news
        ]);
    }
    public function update(Request $request, $id)
    {
        // Tìm tin tức theo id
        $news = News::find($id);
        if (!$news || $news->deleted) {
            return response()->json([
                'code' => 'error',
                'message' => 'Tin tức không tồn tại.'
            ], 404);
        }

        // Kiểm tra và xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|string',
            'author' => 'required|string|max:255',
            'position' => 'nullable|integer',
            'newsCategory' => 'required|exists:newscategories,id',
            'status' => 'required|boolean',
            'featured' => 'required|boolean',
        ]);
        $position = $request->has('position') ? $request->position : $news->position;
        $slug = $news->title !== $validated['title']
            ? generateUniqueSlug($validated['title'], News::class)
            : $news->slug;

        // Cập nhật thông tin tin tức
        $news->fill([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $request->image ?? $news->image,
            'author' => $request->author ?? $news->author,
            'newsCategory' => $request->newsCategory,
            'status' => $request->status,
            'featured' => $request->featured ?? $news->featured,
            'position' => $position,
            'slug' => $slug
        ]);
        if ($news->isDirty()) {
            $news->save();
        }
        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật tin tức thành công.',
            'data' => $news
        ]);
    }

    public function destroy($id)
    {
        $news = News::find($id);
        if (!$news) {
            return response()->json([
                'code' => 'error',
                'message' => 'Tin tức không tồn tại.'
            ], 404);
        }

        $news->delete();
        return response()->json([
            'code' => 'success',
            'message' => 'Xóa tin tức thành công.'
        ]);
    }

    // xóa mềm
    public function softDelete($id)
    {
        $news = News::find($id);

        if (!$news || $news->deleted) {
            return response()->json([
                'code' => 'error',
                'message' => 'Tin tức không tồn tại.'
            ], 404);
        }

        $news->update(['deleted' => true]);

        return response()->json([
            'code' => 'success',
            'message' => 'Xóa mềm tin tức thành công.'
        ]);
    }
    public function restore($id)
    {
        $news = News::where('deleted', true)->find($id);

        if (!$news) {
            return response()->json([
                'code' => 'error',
                'message' => 'Tin tức không tồn tại.'
            ], 404);
        }

        $news->update(['deleted' => false]);

        return response()->json([
            'code' => 'success',
            'message' => 'Khôi phục tin tức thành công.'
        ]);
    }

    public function listNoPagination()
    {
        $list = News::where('deleted', false)
            ->where('status', 1)
            ->orderBy('position', 'asc')
            ->get(['id', 'title']);

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách tin tức.',
            'data' => $list
        ]);
    }

    // cập nhật trạng thái
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $news = News::find($id);
        if (!$news) {
            return response()->json([
                'code' => 'error',
                'message' => 'Bài viết không tồn tại!'
            ], 404);
        }

        $news->status = $request->status;
        $news->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => $news
        ]);
    }

    // cập nhật nỗi bật
    public function updateFeature(Request $request, $id)
    {
        $request->validate([
            'featured' => 'required|boolean',
        ]);

        $news = News::find($id);
        if (!$news) {
            return response()->json([
                'code' => 'error',
                'message' => 'Bài viết không tồn tại!'
            ], 404);
        }

        $news->featured = $request->featured;
        $news->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật trạng thái nổi bật thành công.',
            'data' => $news
        ]);
    }

    // thay đổi vị trí
    public function updatePosition(Request $request, $id)
{
    $request->validate([
        'position' => 'required|integer|min:1',
    ]);

    $news = News::where('deleted', false)->find($id);

    if (!$news) {
        return response()->json([
            'code' => 'error',
            'message' => 'Bài viết không tồn tại.',
        ], 404);
    }

    $news->position = $request->position;
    $news->save();

    return response()->json([
        'code' => 'success',
        'message' => 'Cập nhật vị trí thành công.',
        'data' => $news
    ]);
}
    public function trashNews(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $newsList = News::with('category')
            ->where('deleted', true)
            ->orderBy('position', 'desc')
            ->paginate($perPage);

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách tin tức.',
            'data' => $newsList
        ]);
    }

}
