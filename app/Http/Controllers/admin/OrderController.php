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
            'message' => "Hiển thị danh sách thương hiệu thành công",
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
    public function show($id)
    {
        // Lấy thông tin chi tiết của đơn hàng
        $order = Order::with(['user', 'orderItems.productVariant.product'])
            ->findOrFail($id);

        return response()->json($order);
    }

}
