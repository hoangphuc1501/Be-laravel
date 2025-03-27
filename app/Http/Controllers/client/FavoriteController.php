<?php

namespace App\Http\Controllers\client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    // Thêm vào danh sách yêu thích
    public function addToFavorite(Request $request)
    {
        try {
            Log::info('Dữ liệu nhận được:', $request->all());

            // Kiểm tra dữ liệu đầu vào
            $request->validate([
                'productVariantId' => 'required|exists:productsvariants,id',
                'sizeId' => 'required|exists:sizes,id',
                'colorId' => 'required|exists:colors,id',
            ]);

            // Lấy thông tin người dùng từ JWT
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Người dùng chưa đăng nhập!'
                ], 401);
            }

            // Kiểm tra xem sản phẩm đã có trong danh sách yêu thích chưa
            $favoriteItem = Favorite::where('userId', $user->id)
                ->where('productVariantId', $request->productVariantId)
                ->where('sizeId', $request->sizeId)
                ->where('colorId', $request->colorId)
                ->first();

            if ($favoriteItem) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Sản phẩm đã có trong danh sách yêu thích!'
                ], 400);
            }

            // Thêm vào danh sách yêu thích
            $favoriteItem = Favorite::create([
                'userId' => $user->id,
                'productVariantId' => $request->productVariantId,
                'sizeId' => $request->sizeId,
                'colorId' => $request->colorId
            ]);

            return response()->json([
                'code' => 'success',
                'message' => 'Sản phẩm đã được thêm vào danh sách yêu thích.',
                'favoriteItem' => $favoriteItem
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi server, vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Hiển thị danh sách yêu thích
    public function showFavorites(Request $request)
    {
        try {
            // Lấy thông tin người dùng từ JWT
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Người dùng chưa đăng nhập!'
                ], 401);
            }

            // Lấy danh sách yêu thích của người dùng
            // $favoriteItems = Favorite::where('userId', $user->id)
            //     ->with(['productVariant.product', 'productVariant.images'])
            //     ->get();
            $favoriteItems = Favorite::where('userId', $user->id)
            ->with([
                'productVariant.product',
                'productVariant.images',
                'color:id,name', 
                'size:id,name'     
            ])
            ->get();

            return response()->json([
                'code' => 'success',
                'message' => 'Danh sách sản phẩm yêu thích',
                'favorites' => $favoriteItems
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi server, vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Xóa sản phẩm khỏi danh sách yêu thích
    public function deleteFavoriteItem(Request $request, $favoriteId)
    {
        try {
            // Lấy thông tin người dùng từ JWT
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Người dùng chưa đăng nhập!'
                ], 401);
            }

            // Kiểm tra sản phẩm có trong danh sách yêu thích không
            $favoriteItem = Favorite::where('userId', $user->id)->where('id', $favoriteId)->first();

            if (!$favoriteItem) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Sản phẩm không tồn tại trong danh sách yêu thích!'
                ], 404);
            }

            // Xóa sản phẩm khỏi danh sách yêu thích
            $favoriteItem->delete();

            return response()->json([
                'code' => 'success',
                'message' => 'Sản phẩm đã được xóa khỏi danh sách yêu thích.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi server, vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
