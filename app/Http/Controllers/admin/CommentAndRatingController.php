<?php

namespace App\Http\Controllers\admin;

use App\Models\Comment;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommentAndRatingController extends Controller
{
    public function comments(Request $request, $productId)
    {
        $request->validate([
            'content' => 'required|string',
            'userID' => 'required|exists:users,id',
        ]);

        $comment = Comment::create([
            'content' => $request->content,
            'userID' => $request->user_id,
            'productID' => $productId,
        ]);

        return response()->json($comment, 201);
    }

    public function getComments($productId)
    {
        $comments = Comment::visible()->where('productID', $productId)->get();
        return response()->json($comments);
    }

    public function hideComments($commentId)
    {
        $comment = Comment::findOrFail($commentId);
        $comment->deleted = 1;  // Đánh dấu là ẩn
        $comment->save();

        return response()->json(['message' => 'Comment hidden successfully.']);
    }

    public function unhideComments($commentId)
    {
        $comment = Comment::findOrFail($commentId);
        $comment->deleted = 0;  // Khôi phục lại bình luận
        $comment->save();

        return response()->json(['message' => 'Comment restored successfully.']);
    }
    public function ratings(Request $request, $productId)
    {
        $request->validate([
            'content' => 'required|string',
            'star' => 'required|integer|min:1|max:5',
            'userID' => 'required|exists:users,id',
        ]);

        $rating = Rating::create([
            'content' => $request->content,
            'star' => $request->star,
            'userID' => $request->user_id,
            'productID' => $productId,
        ]);

        return response()->json($rating, 201);
    }

    public function getRatings($productId)
    {
        $ratings = Rating::visible()->where('productID', $productId)->get();
        return response()->json($ratings);
    }

    public function hideRatings($ratingId)
    {
        $rating = Rating::findOrFail($ratingId);
        $rating->deleted = 1;  // Đánh dấu là ẩn
        $rating->save();

        return response()->json(['message' => 'Rating hidden successfully.']);
    }

    public function unhideRatings($ratingId)
    {
        $rating = Rating::findOrFail($ratingId);
        $rating->deleted = 0;  // Khôi phục lại đánh giá
        $rating->save();

        return response()->json(['message' => 'Rating restored successfully.']);
    }
}
