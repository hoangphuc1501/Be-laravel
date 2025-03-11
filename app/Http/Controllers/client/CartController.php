<?php

namespace App\Http\Controllers\client;

use App\Models\Cart;
use App\Models\ProductVariant;
use App\Models\ProductVariants;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\VariationOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{

    // thêm vào giỏ hàng
    public function addToCart(Request $request)
    {
        try {
            Log::info('Dữ liệu nhận được:', $request->all());

            // Kiểm tra dữ liệu đầu vào
            $request->validate([
                'productVariantId' => 'required|exists:productsvariants,id',
                'sizeId' => 'required|exists:sizes,id',
                'colorId' => 'required|exists:colors,id',
                'quantity' => 'required|integer|min:1'
            ]);

            // Lấy thông tin người dùng từ JWT
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Người dùng chưa đăng nhập!'
                ], 401);
            }

            // Kiểm tra tồn kho
            $productVariant = ProductVariants::find($request->productVariantId);
            if (!$productVariant || $request->quantity > $productVariant->stock) {
                return response()->json([
                    'code' => 'error',
                    'message' => "Trong kho không đủ số lượng! Chỉ còn {$productVariant->stock} sản phẩm."
                ], 400);
            }

            // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
            $cartItem = Cart::where('userId', $user->id)
                ->where('productVariantId', $request->productVariantId)
                ->where('sizeId', $request->sizeId)
                ->where('colorId', $request->colorId)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $request->quantity;

                if ($newQuantity > $productVariant->stock) {
                    return response()->json([
                        'code' => 'error',
                        'message' => "Vượt quá số lượng tồn kho! Chỉ còn {$productVariant->stock} sản phẩm."
                    ], 400);
                }

                $cartItem->update(['quantity' => $newQuantity]);

                return response()->json([
                    'code' => 'success',
                    'message' => 'Cập nhật số lượng sản phẩm trong giỏ hàng thành công.',
                    'cartItem' => $cartItem
                ]);
            } else {
                $cartItem = Cart::create([
                    'userId' => $user->id,
                    'productVariantId' => $request->productVariantId,
                    'sizeId' => $request->sizeId,
                    'colorId' => $request->colorId,
                    'quantity' => $request->quantity
                ]);

                return response()->json([
                    'code' => 'success',
                    'message' => 'Sản phẩm đã được thêm vào giỏ hàng.',
                    'cartItem' => $cartItem
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi server, vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // hiển thị giỏ hàng
    public function showCart(Request $request)
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

            // Lấy danh sách giỏ hàng của người dùng với đầy đủ thông tin
            $cartItems = Cart::where('userId', $user->id)
                ->with([
                    'productVariant.product',
                    'productVariant.VariationOptions.color',
                    'productVariant.VariationOptions.size',
                    'productVariant.images'
                ])
                ->get();

            // Kiểm tra giỏ hàng trống
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'code' => 'success',
                    'message' => 'Giỏ hàng trống',
                    'cart' => []
                ], 200);
            }

            // Xử lý dữ liệu trả về
            $formattedCart = $cartItems->map(function ($cart) {
                $variantOption = $cart->productVariant->VariationOptions
                    ->where('sizeId', $cart->sizeId) // Lấy đúng size trong giỏ hàng
                    ->first();
                return [
                    'id' => $cart->id,
                    'product' => [
                        'id' => $cart->productVariant->product->id ?? null,
                        'title' => $cart->productVariant->product->title ?? "Sản phẩm không tồn tại",
                        'slug' => $cart->productVariant->product->slug ?? "#",
                    ],
                    'quantity' => $cart->quantity,
                    'variant' => [
                        'id' => $cart->productVariant->id,
                        'color' => [
                            'id' => optional($variantOption->color)->id ?? null,
                            'name' => optional($variantOption->color)->name ?? "Không xác định"
                        ],
                        'size' => [
                            'id' => optional($variantOption->size)->id ?? null,
                            'name' => optional($variantOption->size)->name ?? "Không xác định"
                        ],
                        'specialPrice' => $cart->productVariant->specialPrice ?? 0,
                        'price' => $cart->productVariant->price ?? 0,
                    ],
                    'image' => $cart->productVariant->images->isNotEmpty() ? $cart->productVariant->images->first()->image : "/default-image.jpg",
                    'subtotal' => ($cart->quantity * ($cart->productVariant->specialPrice ?? 0))
                ];
            });

            return response()->json([
                'code' => 'success',
                'message' => 'Danh sách giỏ hàng',
                'cart' => $formattedCart
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi server, vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // hàm xóa ra khỏi giỏ hàng
    public function deleteCartItem(Request $request, $cartId)
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

            // Kiểm tra sản phẩm có trong giỏ hàng không
            $cartItem = Cart::where('userId', $user->id)->where('id', $cartId)->first();

            if (!$cartItem) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Sản phẩm không tồn tại trong giỏ hàng!'
                ], 404);
            }

            // Xóa sản phẩm khỏi giỏ hàng
            $cartItem->delete();

            // Lấy danh sách giỏ hàng sau khi xóa
            $cartItems = Cart::where('userId', $user->id)
                ->with([
                    'productVariant.product',
                    'productVariant.images',
                    'productVariant.variationOptions.color',
                    'productVariant.variationOptions.size'
                ])
                ->get();

            // Định dạng dữ liệu trả về
            $formattedCart = $cartItems->map(function ($cart) {
                $firstVariantOption = $cart->productVariant->variationOptions->first();
                return [
                    'id' => $cart->id,
                    'product' => [
                        'id' => $cart->productVariant->product->id ?? null,
                        'title' => $cart->productVariant->product->title ?? "Sản phẩm không tồn tại",
                        'slug' => $cart->productVariant->product->slug ?? "#",
                    ],
                    'quantity' => $cart->quantity,
                    'variant' => [
                        'id' => $cart->productVariant->id,
                        'color' => [
                            'id' => optional($firstVariantOption->color)->id ?? null,
                            'name' => optional($firstVariantOption->color)->name ?? "Không xác định"
                        ],
                        'size' => [
                            'id' => optional($firstVariantOption->size)->id ?? null,
                            'name' => optional($firstVariantOption->size)->name ?? "Không xác định"
                        ],
                        'specialPrice' => $cart->productVariant->specialPrice ?? 0,
                        'price' => $cart->productVariant->price ?? 0,
                    ],
                    'image' => $cart->productVariant->images->isNotEmpty() ? $cart->productVariant->images->first()->image : "/default-image.jpg",
                    'subtotal' => ($cart->quantity * ($cart->productVariant->specialPrice ?? 0))
                ];
            });

            return response()->json([
                'code' => 'success',
                'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng.',
                'cart' => $formattedCart
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi server, vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // cập nhật số lượng
    public function updateQuantity(Request $request)
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

            // Kiểm tra dữ liệu đầu vào
            $request->validate([
                'cartId' => 'required|integer|exists:carts,id',
                'quantity' => 'required|integer|min:1',
                'sizeId' => 'nullable|integer'
            ]);

            // Lấy thông tin sản phẩm trong giỏ hàng
            $cartQuery = Cart::where('userId', $user->id)->where('id', $request->cartId);
            if ($request->has('sizeId')) {
                $cartQuery->where('sizeId', $request->sizeId);
            }
    
            $cartItem = $cartQuery->first();


            if (!$cartItem) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Sản phẩm không tồn tại trong giỏ hàng!'
                ], 404);
            }

            // Kiểm tra tồn kho của sản phẩm
            $productVariant = ProductVariants::find($cartItem->productVariantId);
            if (!$productVariant || $request->quantity > $productVariant->stock) {
                return response()->json([
                    'code' => 'error',
                    'message' => "Vượt quá số lượng tồn kho! Chỉ còn {$productVariant->stock} sản phẩm."
                ], 400);
            }

            // Cập nhật số lượng trong giỏ hàng
            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            // Trả về dữ liệu sau khi cập nhật
            return response()->json([
                'code' => 'success',
                'message' => 'Cập nhật số lượng sản phẩm trong giỏ hàng thành công.',
                'updatedCart' => $cartItem
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
