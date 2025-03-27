<?php

namespace App\Http\Controllers\admin;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class OrderController extends Controller
{
    // danh sách đơn hàng
    public function index(Request $request)
    {

        $orders = Order::with('user')
            ->orderBy('createdAt', 'desc')
            ->paginate(10);

        return response()->json([
            'code' => 'success',
            'message' => "Hiển thị danh sách đơn hàng thành công",
            'data' => $orders,
        ], 200);
    }

    // cập nhật trạng thái đơn hàng
    // public function update(Request $request, $id)
    // {
    //     $order = Order::findOrFail($id);
    //     $order->status = $request->status;
    //     $order->save();

    //     return response()->json([
    //         'code' => 'success',
    //         'message' => 'Cập nhật trạng thái thành công',
    //     ], 200);
    // }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $currentStatus = $order->status;
        $newStatus = $request->status;
    
        // Xác định thứ tự trạng thái
        $validStatusFlow = [
            'pending' => ['confirmed', 'canceled'], 
            'confirmed' => ['shipped', 'canceled'],  
            'shipped' => ['completed'], 
            'completed' => [],           
            'canceled' => [] 
        ];
    
        // Kiểm tra nếu trạng thái mới không hợp lệ
        if (!isset($validStatusFlow[$currentStatus]) || !in_array($newStatus, $validStatusFlow[$currentStatus])) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không thể quay lại trạng thái trước đó hoặc cập nhật trạng thái không hợp lệ!',
            ], 400);
        }
    
        // Cập nhật trạng thái nếu hợp lệ
        $order->status = $newStatus;
        $order->save();
    
        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật trạng thái thành công',
            'new_status' => $order->status
        ], 200);
    }



    // chi tiết đơn hàng
    public function show($orderId)
    {
        try {
            // Lấy thông tin đơn hàng kèm thông tin người dùng
            $order = Order::where('id', $orderId)
                ->with([
                    'user', // Thông tin khách hàng
                    'orderItems.productVariant.product',
                    'orderItems.productVariant.variationOptions.color',
                    'orderItems.productVariant.variationOptions.size',
                    'orderItems.productVariant.images'
                ])
                ->first();
    
            // Kiểm tra nếu đơn hàng không tồn tại
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
                'shipping_address' => $order->shippingAddress ?? 'Không có thông tin',
                // Thông tin khách hàng
                'user' => [
                    'id' => $order->user->id ?? null,
                    'name' => $order->user->fullname ?? 'Không xác định',
                    'email' => $order->user->email ?? 'Không xác định',
                    'phone' => $order->user->phone ?? 'Chưa cập nhật',
                    'address' => $order->user->address ?? 'Chưa cập nhật',
                ],
    
                // Danh sách sản phẩm trong đơn hàng
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

}
