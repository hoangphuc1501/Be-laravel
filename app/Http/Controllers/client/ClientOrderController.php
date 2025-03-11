<?php

namespace App\Http\Controllers\client;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientOrderController extends Controller
{
    // tạo đơn hàng
    public function placeOrder(Request $request)
    {
        try {
            DB::beginTransaction(); // Bắt đầu transaction

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Người dùng chưa đăng nhập!'
                ], 401);
            }

            if (!$request->input('shippingAddress')) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Địa chỉ giao hàng không được để trống!'
                ], 400);
            }

            $cartItems = Cart::where('userId', $user->id)->with('productVariant.variationOptions')->get();
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Giỏ hàng trống!'
                ], 400);
            }

            // Tính tổng giá trị đơn hàng
            $totalPrice = 0;
            foreach ($cartItems as $cart) {
                $price = $cart->productVariant->specialPrice ?? $cart->productVariant->price;
                $totalPrice += $cart->quantity * $price;
            }

            // Tạo đơn hàng
            $order = Order::create([
                'userId' => $user->id,
                'code' => 'ORD' . time(),
                'note' => $request->input('note', ''),
                'totalPrice' => $totalPrice,
                'shippingAddress' => $request->input('shippingAddress'),
                'paymentStatus' => 'pending',
                'paymentMethod' => $request->input('paymentMethod', 'COD'),
                'status' => 'pending',
                'createdAt' => now(),
                'updatedAt' => now(),
            ]);

            // Thêm sản phẩm vào đơn hàng
            foreach ($cartItems as $cart) {
                $variant = $cart->productVariant;
                $price = $variant->specialPrice ?? $variant->price;

                // Lấy thông tin size và color từ bảng VariantOptions
                $variantOption = $variant->variationOptions->whereNotNull('sizeId')->whereNotNull('colorId')->first();
                $sizeId = optional($variantOption)->sizeId;
                $colorId = optional($variantOption)->colorId;


                OrderItem::create([
                    'orderId' => $order->id,
                    'productVariantId' => $variant->id,
                    'sizeId' => $sizeId,
                    'colorId' => $colorId,
                    'price' => $price,
                    'quantity' => $cart->quantity,
                    'subTotal' => $cart->quantity * $price,
                    'createdAt' => now(),
                    'updatedAt' => now(),
                ]);

                // Giảm số lượng sản phẩm trong kho
                $variant->stock -= $cart->quantity;
                if ($variant->stock < 0) {
                    return response()->json([
                        'code' => 'error',
                        'message' => 'Sản phẩm ' . $variant->id . ' không đủ hàng trong kho!',
                    ], 400);
                }
                $variant->save();
            }

            // Xóa giỏ hàng sau khi đặt hàng thành công
            Cart::where('userId', $user->id)->delete();

            DB::commit(); // Lưu thay đổi vào DB

            return response()->json([
                'code' => 'success',
                'message' => 'Đặt hàng thành công!',
                'orderId' => $order->id,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback nếu có lỗi

            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi server, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // chi tiết đơn hàng
    public function getOrderDetail($orderId)
    {
        try {
            // Lấy thông tin người dùng
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Người dùng chưa đăng nhập!',
                ], 401);
            }

            // Lấy thông tin đơn hàng
            $order = Order::where('id', $orderId)
                ->where('userId', $user->id)
                ->with([
                    'orderItems.productVariant.product',
                    'orderItems.productVariant.variationOptions.color',
                    'orderItems.productVariant.variationOptions.size',
                    'orderItems.productVariant.images'
                ])
                ->first();

            if (!$order) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Không tìm thấy đơn hàng!',
                ], 404);
            }
            // Danh sách trạng thái đơn hàng
            $statusLabels = [
                'pending' => 'Chờ xác nhận',
                'confirmed' => 'Đã xác nhận',
                'shipping' => 'Đang vận chuyển',
                'delivered' => 'Đã giao hàng',
                'canceled' => 'Đã hủy'
            ];
            // Xử lý dữ liệu trả về
            $formattedOrder = [
                'id' => $order->id,
                'order_code' => $order->code,
                'total_price' => $order->totalPrice,
                'payment_status' => $order->paymentStatus,
                'payment_method' => $order->paymentMethod,
                'status' => [
                    'key' => $order->status,
                    'label' => $statusLabels[$order->status] ?? 'Không xác định'
                ],
                'created_at' => $order->createdAt->format('Y-m-d H:i:s'),
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->productVariant->product->id ?? null,
                            'title' => $item->productVariant->product->title ?? "Sản phẩm không tồn tại",
                            'slug' => $item->productVariant->product->slug ?? "#",
                        ],
                        'quantity' => $item->quantity,
                        'variant' => [
                            'id' => $item->productVariant->id,
                            'color' => [
                                'id' => optional($item->productVariant->variationOptions->first())->color->id ?? null,
                                'name' => optional($item->productVariant->variationOptions->first())->color->name ?? "Không xác định",
                            ],
                            'size' => [
                                'id' => optional($item->productVariant->variationOptions->first())->size->id ?? null,
                                'name' => optional($item->productVariant->variationOptions->first())->size->name ?? "Không xác định",
                            ],
                            'special_price' => $item->productVariant->specialPrice ?? 0,
                            'price' => $item->productVariant->price ?? 0,
                        ],
                        'image' => $item->productVariant->images->isNotEmpty()
                            ? $item->productVariant->images->first()->image
                            : "/default-image.jpg",
                        'subtotal' => $item->subTotal,
                    ];
                }),
            ];

            return response()->json([
                'code' => 'success',
                'message' => 'Chi tiết đơn hàng',
                'order' => $formattedOrder
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi server, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // danh sách đơn hàng
//     public function index(Request $request)
// {
//     // Lấy user từ JWT
//     $user = auth()->user();

//     // Kiểm tra nếu user tồn tại
//     if (!$user) {
//         return response()->json([
//             'code' => 'error',
//             'message' => 'Người dùng chưa được xác thực',
//         ], 401);
//     }

//     // Lấy danh sách đơn hàng của user đang đăng nhập
//     $orders = Order::where('userId', $user->id)
//         ->orderBy('createdAt', 'desc')
//         ->paginate(10);

//     return response()->json([
//         'code' => 'success',
//         'message' => 'Hiển thị danh sách đơn hàng thành công',
//         'data' => $orders,
//     ], 200);
// }

public function index(Request $request)
{
    $user = auth()->user();
    
    if (!$user) {
        return response()->json([
            'code' => 'error',
            'message' => 'Người dùng chưa được xác thực',
        ], 401);
    }

    // Lấy giá trị status từ request, nếu không có thì lấy "all"
    $status = $request->query('status', 'all');

    // Query đơn hàng theo user
    $query = Order::where('userId', $user->id);

    // Nếu có lọc theo status, áp dụng điều kiện
    if ($status !== 'all') {
        $query->where('status', $status);
    }

    $orders = $query->orderBy('createdAt', 'desc')->paginate(10);

    return response()->json([
        'code' => 'success',
        'message' => 'Hiển thị danh sách đơn hàng thành công',
        'data' => $orders,
    ], 200);
}
}
