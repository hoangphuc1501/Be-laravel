<?php

namespace App\Http\Controllers\client;


use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    // Lấy danh sách bình luận
    public function getComments($productId)
    {
        $comments = Comment::with('user')
            ->where('productId', $productId)
            ->orderBy('createdAt', 'desc')
            ->get();
        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách bình luận.',
            'data' => $comments
        ], 200);
    }

    // Người dùng có thể bình luận mà không cần mua hàng
    public function storeComment(Request $request)
    {
        $user = Auth::user();

        $comment = Comment::create([
            'productId' => $request->productId,
            'userId' => $user->id,
            'content' => $request->content,
        ]);

        $comment->load('user');
        return response()->json([
            'code' => 'success',
            'message' => 'Bình luận thành công.',
            'data' => $comment
        ], 200);
    }

    // Xóa bình luận người user
    public function deleteComment($id)
    {
        $comment = Comment::where('id', $id)
            ->where('userId', Auth::id())
            ->first();
        if (!$comment) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không tìm thấy hoặc không có quyền xóa'
            ], 403);
        }
        $comment->delete();
        return response()->json([
            'code' => 'error',
            'message' => 'Đã xóa bình luận'
        ], 403);
    }

    // Lấy danh sách tất cả bình luận trong admin
    public function getAllComments(Request $request)
    {

        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');

        $query = Comment::with(['user:id,fullname', 'product:id,title'])
            ->orderBy('createdAt', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%$search%")
                    ->orWhereHas('user', function ($qUser) use ($search) {
                        $qUser->where('fullname', 'like', "%$search%");
                    })
                    ->orWhereHas('product', function ($qProduct) use ($search) {
                        $qProduct->where('title', 'like', "%$search%");
                    });
            });
        }
        $comments = $query->paginate($perPage);
        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách tất cả bình luận.',
            'data' => $comments
        ], 200);
    }

    // xóa trong admin
    public function deleteCommentAdmin($id)
    {
        // Tìm bình luận theo ID
        $comment = Comment::find($id);

        // Kiểm tra xem bình luận có tồn tại không
        if (!$comment) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không tìm thấy bình luận'
            ], 404);
        }

        // Xóa bình luận
        $comment->delete();

        return response()->json([
            'code' => 'success',
            'message' => 'Bình luận đã được xóa thành công'
        ], 200);
    }

}
