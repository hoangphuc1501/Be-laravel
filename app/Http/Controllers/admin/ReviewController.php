<?php

namespace App\Http\Controllers\admin;

use App\Models\Review;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Lấy danh sách đánh giá của sản phẩm
    public function getReviews($productId) {
        $reviews = Review::with('user')
        ->where('productId', $productId)
        ->orderBy('createdAt', 'desc')
        ->get();
        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách đánh giá.',
            'data' => $reviews
        ], 200);
    }

    // Chỉ cho phép đánh giá nếu đã mua hàng
    public function storeReview(Request $request)
    {
        $user = Auth::user();
    
        // Kiểm tra xem người dùng đã mua sản phẩm này chưa
        $hasPurchased = Order::where('userId', $user->id)
            ->where('status', 'completed') 
            ->where('paymentStatus', 'paid')
            ->whereHas('orderItems', function ($query) use ($request) {
                $query->whereHas('productVariant', function ($subQuery) use ($request) {
                    $subQuery->where('ProductID', $request->productId); // Kiểm tra xem sản phẩm có trong đơn hàng không
                });
            })
            ->exists();
    
        if (!$hasPurchased) {
            return response()->json([
                'code' => 'error',
                'message' => 'Bạn chỉ có thể đánh giá sản phẩm sau khi mua hàng thành công.'
            ], 403);
        }
    
        // Kiểm tra xem đã có đánh giá từ user cho sản phẩm này chưa
        $existingReview = Review::where('userId', $user->id)
            ->where('productId', $request->productId)
            ->first();
    
        if ($existingReview) {
            return response()->json([
                'code' => 'error',
                'message' => 'Bạn đã đánh giá sản phẩm này rồi!'
            ], 403);
        }
    
        // Tạo đánh giá mới 
        $review = Review::create([
            'productId' => $request->productId,
            'userId' => $user->id,
            'content' => $request->content,
            'star' => $request->star,
        ]);
    
        $review->load('user');
        return response()->json([
            'code' => 'success',
            'message' => 'Đánh giá thành công!',
            'data' => $review
        ], 200);
    }
    

    

    // Xóa đánh giá (chỉ chủ sở hữu mới được xóa)
    public function deleteReview($id)
    {
        $review = Review::where('id', $id)->where('userId', Auth::id())->first();
        if (!$review) {
            return response()->json(['error' => 'Không tìm thấy hoặc không có quyền xóa'], 403);
        }
        $review->delete();
        return response()->json(['message' => 'Đã xóa đánh giá']);
    }


    // danh sách đánh giá trong admin
    public function getAllReviews(Request $request)
{
    // phân quyền
    $this->authorize('viewAny', Review::class);
    $perPage = $request->input('per_page', 10);
    $search = $request->input('search');

    $query = Review::with(['user:id,fullname,email', 'product:id,title'])
        ->orderBy('createdAt', 'desc');

    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('content', 'like', "%$search%")
                ->orWhereHas('user', function ($qUser) use ($search) {
                    $qUser->where('fullname', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                })
                ->orWhereHas('product', function ($qProduct) use ($search) {
                    $qProduct->where('title', 'like', "%$search%");
                });
        });
    }

    $reviews = $query->paginate($perPage);

    return response()->json([
        'code' => 'success',
        'message' => 'Danh sách tất cả đánh giá.',
        'data' => $reviews
    ], 200);
}

public function deleteReviewAdmin($id)
{
    // Tìm đánh giá theo ID
    $review = Review::find($id);
// phân quyền
$this->authorize('delete', $review);
    // Kiểm tra xem đánh giá có tồn tại không
    if (!$review) {
        return response()->json([
            'code' => 'error',
            'message' => 'Không tìm thấy đánh giá'
        ], 404);
    }

    // Xóa đánh giá
    $review->delete();

    return response()->json([
        'code' => 'success',
        'message' => 'Đánh giá đã được xóa thành công'
    ], 200);
}
}



// class ReviewController extends Controller
// {
//     // Lấy danh sách bình luận theo sản phẩm
//     public function index($productId)
//     {
//         $reviews = Review::with('user')
//             ->where('productId', $productId)
//             ->orderBy('createdAt', 'desc')
//             ->get();
//         return response()->json([
//             'code' => 'success',
//             'message' => 'Danh sách bình luận.',
//             'data' => $reviews
//         ], 200);
//     }

//     // Thêm bình luận
//     public function store(Request $request)
//     {
//         $request->validate([
//             'productId' => 'required|exists:products,id',
//             'content' => 'required|string',
//             'star' => 'required|integer|min:1|max:5',
//         ]);

//         $review = Review::create([
//             'userId' => Auth::id(),
//             'productId' => $request->productId,
//             'content' => $request->content,
//             'star' => $request->star,
//         ]);

//         return response()->json([
//             'code' => 'success',
//             'message' => 'Bình luận thành công.',
//             'data' => $review
//         ], 200);
//     }

//     // Xóa bình luận
//     public function destroy($id)
//     {
//         $review = Review::findOrFail($id);
//         if ($review->userId !== Auth::id()) {
//             return response()->json(['error' => 'Unauthorized'], 403);
//         }
//         $review->delete();
//         return response()->json(['message' => 'Deleted successfully']);
//     }

// }
