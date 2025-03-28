<?php

namespace App\Http\Controllers\client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\news;
use App\Models\NewsCategory;

class ClientNewsController extends Controller
{
    public function index()
    {
        try {
            $newsList = news::with('category:id,name')
                ->where('deleted', 0)
                ->where('status', 1)
                ->orderByDesc('position')
                ->get(['id', 'title', 'content', 'image', 'slug', 'author', 'createdAt']);

            return response()->json([
                'code' => 'success',
                'message' => 'Hiển thị danh sách tin tức thành công.',
                'newsList' => $newsList
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function detail($slug)
    {
        try {
            $newsDetail = News::with('category:id,name')
                ->where('slug', $slug)
                ->where('deleted', 0)
                ->where('status', 1)
                ->first(['id', 'title', 'content', 'image', 'slug', 'author', 'createdAt', 'views', 'likes', 'shares']);

            if (!$newsDetail) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Tin tức không tồn tại!'
                ], 404);
            }

            return response()->json([
                'code' => 'success',
                'message' => 'Hiển thị tin tức thành công.',
                'newsDetail' => $newsDetail
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getNewsByCategory($slug)
{
    try {
        $category = NewsCategory::where('slug', $slug)
            ->where('deleted', 0)
            ->where('status', 1)
            ->first(['id', 'name', 'slug']);

        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục không tồn tại!'
            ], 404);
        }

        $newsList = News::with('category:id,name')
            ->where('newsCategory', $category->id)
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderByDesc('position')
            ->get(['id', 'title', 'content', 'image', 'slug', 'author', 'createdAt']);

        return response()->json([
            'code' => 'success',
            'message' => 'Hiển thị danh sách tin tức theo danh mục thành công.',
            'newsList' => $newsList
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
public function featuredNews()
{
    try {
        $newsList = News::with('category:id,name')
            ->where('deleted', 0)
            ->where('status', 1)
            ->where('featured', 1)
            ->orderByDesc('position')
            ->limit(8)
            ->get(['id', 'title', 'content', 'image', 'slug', 'author', 'createdAt', 'featured', 'views']);

        return response()->json([
            'code' => 'success',
            'message' => 'Hiển thị danh sách tin tức nổi bật thành công.',
            'newsList' => $newsList
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function categoryNews()
{
    try {
        $categories = NewsCategory::where('deleted', 0)
            ->where('status', 1)
            ->orderBy('position', 'ASC')
            ->get(['id', 'name', 'image', 'description', 'slug']);

        return response()->json([
            'code' => 'success',
            'message' => 'Hiển thị danh sách danh mục tin tức thành công.',
            'categories' => $categories
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function newsHomePage()
{
    try {
        $newsList = News::with('category:id,name')
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderByDesc('position')
            ->limit(8)
            ->get(['id', 'title', 'content', 'image', 'slug', 'author', 'createdAt']);

        return response()->json([
            'code' => 'success',
            'message' => 'Hiển thị danh sách tin tức thành công.',
            'newsList' => $newsList
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


}
