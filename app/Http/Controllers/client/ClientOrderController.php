<?php

namespace App\Http\Controllers\client;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientOrderController extends Controller
{
    // tạo đơn hàng tiền mặt

    // public function placeOrder(Request $request)
    // {
    //     try {
    //         DB::beginTransaction(); // Bắt đầu transaction

    //         $user = Auth::user();
    //         if (!$user) {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Người dùng chưa đăng nhập!'
    //             ], 401);
    //         }

    //         if (!$request->input('shippingAddress')) {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Địa chỉ giao hàng không được để trống!'
    //             ], 400);
    //         }

    //         $cartItems = Cart::where('userId', $user->id)->with('productVariant.variationOptions')->get();
    //         if ($cartItems->isEmpty()) {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Giỏ hàng trống!'
    //             ], 400);
    //         }

    //         // Tính tổng giá trị đơn hàng
    //         $totalPrice = 0;
    //         foreach ($cartItems as $cart) {
    //             $price = $cart->productVariant->specialPrice ?? $cart->productVariant->price;
    //             $totalPrice += $cart->quantity * $price;
    //         }

    //         // Tạo đơn hàng
    //         $order = Order::create([
    //             'userId' => $user->id,
    //             'code' => 'ORD' . time(),
    //             'note' => $request->input('note', ''),
    //             'totalPrice' => $totalPrice,
    //             'shippingAddress' => $request->input('shippingAddress'),
    //             'paymentStatus' => 'pending',
    //             'paymentMethod' => $request->input('paymentMethod', 'Thanh toán khi nhận hàng'),
    //             'status' => 'pending',
    //             'createdAt' => now(),
    //             'updatedAt' => now(),
    //         ]);

    //         // Thêm sản phẩm vào đơn hàng
    //         foreach ($cartItems as $cart) {
    //             $variant = $cart->productVariant;
    //             $price = $variant->specialPrice ?? $variant->price;

    //             // Lấy thông tin size và color từ bảng VariantOptions
    //             $variantOption = $variant->variationOptions->whereNotNull('sizeId')->whereNotNull('colorId')->first();
    //             $sizeId = optional($variantOption)->sizeId;
    //             $colorId = optional($variantOption)->colorId;


    //             OrderItem::create([
    //                 'orderId' => $order->id,
    //                 'productVariantId' => $variant->id,
    //                 'sizeId' => $sizeId,
    //                 'colorId' => $colorId,
    //                 'price' => $price,
    //                 'quantity' => $cart->quantity,
    //                 'subTotal' => $cart->quantity * $price,
    //                 'createdAt' => now(),
    //                 'updatedAt' => now(),
    //             ]);

    //             // Giảm số lượng sản phẩm trong kho
    //             $variant->stock -= $cart->quantity;
    //             if ($variant->stock < 0) {
    //                 return response()->json([
    //                     'code' => 'error',
    //                     'message' => 'Sản phẩm ' . $variant->id . ' không đủ hàng trong kho!',
    //                 ], 400);
    //             }
    //             $variant->save();
    //         }

    //         // Xóa giỏ hàng sau khi đặt hàng thành công
    //         Cart::where('userId', $user->id)->delete();

    //         DB::commit(); // Lưu thay đổi vào DB

    //         return response()->json([
    //             'code' => 'success',
    //             'message' => 'Đặt hàng thành công!',
    //             'orderId' => $order->id,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack(); // Rollback nếu có lỗi

    //         return response()->json([
    //             'code' => 'error',
    //             'message' => 'Lỗi server, vui lòng thử lại!',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }



    //     public function placeOrder(Request $request)
// {
//     try {
//         DB::beginTransaction(); // Bắt đầu transaction

    //         $user = Auth::user();
//         if (!$user) {
//             return response()->json([
//                 'code' => 'error',
//                 'message' => 'Người dùng chưa đăng nhập!'
//             ], 401);
//         }

    //         if (!$request->input('shippingAddress')) {
//             return response()->json([
//                 'code' => 'error',
//                 'message' => 'Địa chỉ giao hàng không được để trống!'
//             ], 400);
//         }

    //         $cartItems = Cart::where('userId', $user->id)->with('productVariant.variationOptions')->get();
//         if ($cartItems->isEmpty()) {
//             return response()->json([
//                 'code' => 'error',
//                 'message' => 'Giỏ hàng trống!'
//             ], 400);
//         }

    //         // Tính tổng giá trị đơn hàng
//         $totalPrice = 0;
//         foreach ($cartItems as $cart) {
//             $price = $cart->productVariant->specialPrice ?? $cart->productVariant->price;
//             $totalPrice += $cart->quantity * $price;
//         }

    //         // Tạo đơn hàng với trạng thái `pending`
//         $order = Order::create([
//             'userId' => $user->id,
//             'code' => 'ORD' . time(),
//             'note' => $request->input('note', ''),
//             'totalPrice' => $totalPrice,
//             'shippingAddress' => $request->input('shippingAddress'),
//             'paymentStatus' => 'pending',
//             'paymentMethod' => $request->input('paymentMethod', 'Thanh toán khi nhận hàng'),
//             'status' => 'pending',
//             'createdAt' => now(),
//             'updatedAt' => now(),
//         ]);

    //         // Nếu người dùng chọn thanh toán ZaloPay, gọi API ZaloPay để tạo đơn hàng
//         if ($request->input('paymentMethod') === "Thanh toán bằng ZaloPay") {
//             $zalopayResponse = $this->createZaloPayPayment($order);

    //             if ($zalopayResponse['return_code'] == 1) {
//                 DB::commit();
//                 return response()->json([
//                     'code' => 'success',
//                     'message' => 'Đơn hàng ZaloPay được tạo thành công!',
//                     'order_url' => $zalopayResponse['order_url'],
//                 ]);
//             } else {
//                 DB::rollBack();
//                 return response()->json([
//                     'code' => 'error',
//                     'message' => 'Không thể tạo giao dịch ZaloPay!',
//                     'error' => $zalopayResponse['return_message'],
//                 ], 400);
//             }
//         }

    //         // Nếu không phải ZaloPay, tiếp tục xử lý đơn hàng bình thường
//         foreach ($cartItems as $cart) {
//             $variant = $cart->productVariant;
//             $price = $variant->specialPrice ?? $variant->price;

    //             // Lấy thông tin size và color từ bảng VariantOptions
//             $variantOption = $variant->variationOptions->whereNotNull('sizeId')->whereNotNull('colorId')->first();
//             $sizeId = optional($variantOption)->sizeId;
//             $colorId = optional($variantOption)->colorId;

    //             OrderItem::create([
//                 'orderId' => $order->id,
//                 'productVariantId' => $variant->id,
//                 'sizeId' => $sizeId,
//                 'colorId' => $colorId,
//                 'price' => $price,
//                 'quantity' => $cart->quantity,
//                 'subTotal' => $cart->quantity * $price,
//                 'createdAt' => now(),
//                 'updatedAt' => now(),
//             ]);

    //             // Giảm số lượng sản phẩm trong kho
//             $variant->stock -= $cart->quantity;
//             if ($variant->stock < 0) {
//                 return response()->json([
//                     'code' => 'error',
//                     'message' => 'Sản phẩm ' . $variant->id . ' không đủ hàng trong kho!',
//                 ], 400);
//             }
//             $variant->save();
//         }

    //         // Xóa giỏ hàng sau khi đặt hàng thành công
//         Cart::where('userId', $user->id)->delete();

    //         DB::commit();

    //         return response()->json([
//             'code' => 'success',
//             'message' => 'Đặt hàng thành công!',
//             'orderId' => $order->id,
//         ], 200);
//     } catch (\Exception $e) {
//         DB::rollBack();
//         return response()->json([
//             'code' => 'error',
//             'message' => 'Lỗi server, vui lòng thử lại!',
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// }

    // public function placeOrder(Request $request)
    // {
    //     try {
    //         DB::beginTransaction(); // Bắt đầu transaction

    //         $user = Auth::user();
    //         if (!$user) {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Người dùng chưa đăng nhập!'
    //             ], 401);
    //         }

    //         if (!$request->input('shippingAddress')) {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Địa chỉ giao hàng không được để trống!'
    //             ], 400);
    //         }

    //         $cartItems = Cart::where('userId', $user->id)->with('productVariant.variationOptions')->get();
    //         if ($cartItems->isEmpty()) {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Giỏ hàng trống!'
    //             ], 400);
    //         }

    //         // Tính tổng giá trị đơn hàng
    //         $totalPrice = 0;
    //         foreach ($cartItems as $cart) {
    //             $price = $cart->productVariant->specialPrice ?? $cart->productVariant->price;
    //             $totalPrice += $cart->quantity * $price;
    //         }

    //     // Tạo mã đơn hàng `app_trans_id` cho ZaloPay
    //         $appTransId = date("ymd") . "_" . time(); // Format: yymmdd_timestamp
    //         $isZaloPay = $request->input('paymentMethod') === "Thanh toán bằng ZaloPay";
    //         $orderCode = $isZaloPay ? date("ymd") . "_" . time() : "ORD" . time();

    //         $paymentStatus = 'pending';
    //         // Tạo đơn hàng với trạng thái `pending`
    //         $order = Order::create([
    //             'userId' => $user->id,
    //             'code' => $orderCode,
    //             'note' => $request->input('note', ''),
    //             'totalPrice' => $totalPrice,
    //             'shippingAddress' => $request->input('shippingAddress'),
    //             'paymentStatus' => 'pending',
    //             'paymentMethod' => $request->input('paymentMethod', 'Thanh toán khi nhận hàng'),
    //             'status' => 'pending',
    //             'createdAt' => now(),
    //             'updatedAt' => now(),
    //         ]);

    //         // Nếu người dùng chọn thanh toán ZaloPay, gọi API ZaloPay để tạo đơn hàng
    //         if ($request->input('paymentMethod') === "Thanh toán bằng ZaloPay") {
    //             $zalopayResponse = $this->createZaloPayPayment($order);
    //             Log::error("Phản hồi từ ZaloPay", ['response' => $zalopayResponse]);
    //             if ($zalopayResponse['return_code'] == 1) {
    //                 DB::commit();
    //                 return response()->json([
    //                     'code' => 'success',
    //                     'message' => 'Đơn hàng ZaloPay được tạo thành công!',
    //                     'order_url' => $zalopayResponse['order_url'],
    //                     'zp_trans_token' => $zalopayResponse['zp_trans_token'],
    //                     'app_trans_id' => $appTransId,
    //                 ]);
    //             } else {
    //                 Log::warning("Tạo đơn hàng ZaloPay thất bại", ['error' => $zalopayResponse['return_message']]);
    //                 DB::rollBack();
    //                 return response()->json([
    //                     'code' => 'error',
    //                     'message' => 'Không thể tạo giao dịch ZaloPay!',
    //                     'error' => $zalopayResponse['return_message'],
    //                 ], 400);
    //             }
    //         }

    //         DB::commit();
    //         return response()->json([
    //             'code' => 'success',
    //             'message' => 'Đặt hàng thành công!',
    //             'orderId' => $order->id,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'code' => 'error',
    //             'message' => 'Lỗi server, vui lòng thử lại!',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function placeOrder(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Người dùng chưa đăng nhập!'
                ], 401);
            }
            Log::info("🚀 Dữ liệu nhận từ Frontend:", $request->all());
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
            $totalPrice = $cartItems->sum(function ($cart) {
                return $cart->quantity * ($cart->productVariant->specialPrice ?? $cart->productVariant->price);
            });

            // Xử lý voucher (nếu có)
            $discountAmount = 0;
            $voucherId = null;
            if ($request->filled('code')) {
                $voucher = Voucher::where('code', $request->code)
                    ->where('status', 'active')
                    ->where('deleted', false)
                    ->first();

                if (!$voucher) {
                    return response()->json([
                        'code' => 'error',
                        'message' => 'Voucher không hợp lệ hoặc đã hết hạn.'
                    ], 400);
                }

                if ($totalPrice < $voucher->minOrderValue) {
                    return response()->json([
                        'code' => 'error',
                        'message' => 'Đơn hàng không đủ điều kiện áp dụng voucher.'
                    ], 400);
                }

                $discountAmount = ($voucher->discountType == 1)
                    ? min($totalPrice * ($voucher->discountValue / 100), $voucher->maxDiscount)
                    : min($voucher->discountValue, $voucher->maxDiscount ?? $voucher->discountValue);

                $totalPrice -= $discountAmount;
                $voucherId = $voucher->id;
            }
            Log::info("📌 Giá trị voucherId trước khi lưu đơn hàng:", ['voucherId' => $voucherId]);

            // Xác định phương thức thanh toán
            $isZaloPay = $request->input('paymentMethod') === "Thanh toán bằng ZaloPay";
            $orderCode = $isZaloPay ? date("ymd") . "_" . time() : "ORD" . time(); // ZaloPay dùng `app_trans_id`


            // Tạo đơn hàng trước với trạng thái `pending`
            $order = Order::create([
                'userId' => $user->id,
                'code' => trim($orderCode),
                'note' => $request->input('note', ''),
                'totalPrice' => $totalPrice,
                'shippingAddress' => $request->input('shippingAddress'),
                'paymentStatus' => 'pending',
                'paymentMethod' => $request->input('paymentMethod', 'COD'),
                'status' => 'pending',
                'voucherId' => $voucherId,
                'createdAt' => now(),
                'updatedAt' => now(),
            ]);

            // Lưu từng sản phẩm trong `orderitems`
            foreach ($cartItems as $cart) {
                OrderItem::create([
                    'orderId' => $order->id,
                    'productVariantId' => $cart->productVariantId,
                    'sizeId' => $cart->sizeId,
                    'colorId' => $cart->colorId,
                    'price' => $cart->productVariant->specialPrice ?? $cart->productVariant->price,
                    'quantity' => $cart->quantity,
                    'subTotal' => $cart->quantity * ($cart->productVariant->specialPrice ?? $cart->productVariant->price),
                    'createdAt' => now(),
                    'updatedAt' => now(),
                ]);
            }
            Log::info("✅ Đã lưu orderitems cho đơn hàng #{$order->id}");
            // Nếu chọn ZaloPay, gọi API thanh toán
            if ($isZaloPay) {
                $zalopayResponse = $this->createZaloPayPayment($orderCode, $totalPrice);
                Log::info("🔹 Phản hồi từ ZaloPay:", ['response' => $zalopayResponse]);

                if ($zalopayResponse['return_code'] != 1) {
                    Log::warning("⚠️ Thanh toán ZaloPay thất bại", ['error' => $zalopayResponse['return_message']]);
                    DB::rollBack();
                    return response()->json([
                        'code' => 'error',
                        'message' => 'Không thể tạo giao dịch ZaloPay!',
                        'error' => $zalopayResponse['return_message'],
                    ], 400);
                }

                // Lưu token giao dịch ZaloPay để kiểm tra sau
                $order->update([
                    'zp_trans_token' => $zalopayResponse['zp_trans_token'],
                ]);
            } else {

                // Nếu có voucher, cập nhật số lần sử dụng
                if ($voucherId) {
                    Voucher::where('id', $voucherId)->increment('numberOfUses');
                }

                // Nếu là COD, xóa giỏ hàng ngay lập tức
                Cart::where('userId', $user->id)->delete();
            }

            DB::commit();

            return response()->json([
                'code' => 'success',
                'message' => $isZaloPay ? 'Đơn hàng ZaloPay đã được thanh toán!' : 'Đặt hàng thành công!',
                'order_url' => $isZaloPay ? $zalopayResponse['order_url'] : null,
                'zp_trans_token' => $isZaloPay ? $zalopayResponse['zp_trans_token'] : null,
                'app_trans_id' => $isZaloPay ? $orderCode : null,
                'orderId' => $order->id,
                'discountAmount' => $discountAmount
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Lỗi khi tạo đơn hàng", ['error' => $e->getMessage()]);
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi server, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

//     public function placeOrder(Request $request)
// {
//     try {
//         DB::beginTransaction();

//         $user = Auth::user();
//         if (!$user) {
//             return response()->json([
//                 'code' => 'error',
//                 'message' => 'Người dùng chưa đăng nhập!'
//             ], 401);
//         }

//         Log::info("🚀 Dữ liệu nhận từ Frontend:", $request->all());

//         if (!$request->input('shippingAddress')) {
//             return response()->json([
//                 'code' => 'error',
//                 'message' => 'Địa chỉ giao hàng không được để trống!'
//             ], 400);
//         }

//         $cartItems = Cart::where('userId', $user->id)->with('productVariant.variationOptions')->get();
//         if ($cartItems->isEmpty()) {
//             return response()->json([
//                 'code' => 'error',
//                 'message' => 'Giỏ hàng trống!'
//             ], 400);
//         }

//         $totalPrice = $cartItems->sum(function ($cart) {
//             return $cart->quantity * ($cart->productVariant->specialPrice ?? $cart->productVariant->price);
//         });

//         $discountAmount = 0;
//         $voucherId = null;
//         if ($request->filled('code')) {
//             $voucher = Voucher::where('code', $request->code)
//                 ->where('status', 'active')
//                 ->where('deleted', false)
//                 ->first();

//             if (!$voucher) {
//                 return response()->json([
//                     'code' => 'error',
//                     'message' => 'Voucher không hợp lệ hoặc đã hết hạn.'
//                 ], 400);
//             }

//             if ($totalPrice < $voucher->minOrderValue) {
//                 return response()->json([
//                     'code' => 'error',
//                     'message' => 'Đơn hàng không đủ điều kiện áp dụng voucher.'
//                 ], 400);
//             }

//             $discountAmount = ($voucher->discountType == 1)
//                 ? min($totalPrice * ($voucher->discountValue / 100), $voucher->maxDiscount)
//                 : min($voucher->discountValue, $voucher->maxDiscount ?? $voucher->discountValue);

//             $totalPrice -= $discountAmount;
//             $voucherId = $voucher->id;
//         }

//         $isZaloPay = $request->input('paymentMethod') === "Thanh toán bằng ZaloPay";
//         $isVNPay = $request->input('paymentMethod') === "Thanh toán bằng VNPay";
//         $orderCode = ($isZaloPay || $isVNPay) ? date("ymd") . "_" . time() : "ORD" . time();

//         $order = Order::create([
//             'userId' => $user->id,
//             'code' => trim($orderCode),
//             'note' => $request->input('note', ''),
//             'totalPrice' => $totalPrice,
//             'shippingAddress' => $request->input('shippingAddress'),
//             'paymentStatus' => 'pending',
//             'paymentMethod' => $request->input('paymentMethod', 'COD'),
//             'status' => 'pending',
//             'voucherId' => $voucherId,
//             'createdAt' => now(),
//             'updatedAt' => now(),
//         ]);

//         foreach ($cartItems as $cart) {
//             OrderItem::create([
//                 'orderId' => $order->id,
//                 'productVariantId' => $cart->productVariantId,
//                 'sizeId' => $cart->sizeId,
//                 'colorId' => $cart->colorId,
//                 'price' => $cart->productVariant->specialPrice ?? $cart->productVariant->price,
//                 'quantity' => $cart->quantity,
//                 'subTotal' => $cart->quantity * ($cart->productVariant->specialPrice ?? $cart->productVariant->price),
//                 'createdAt' => now(),
//                 'updatedAt' => now(),
//             ]);
//         }

//         if ($isZaloPay) {
//             $zalopayResponse = $this->createZaloPayPayment($orderCode, $totalPrice);
//             if ($zalopayResponse['return_code'] != 1) {
//                 DB::rollBack();
//                 return response()->json([
//                     'code' => 'error',
//                     'message' => 'Không thể tạo giao dịch ZaloPay!',
//                     'error' => $zalopayResponse['return_message'],
//                 ], 400);
//             }
//             $order->update(['zp_trans_token' => $zalopayResponse['zp_trans_token']]);
//         } elseif ($isVNPay) {
//             $vnpayUrl = $this->createVNPayPayment($orderCode, $totalPrice, $order->id);
//             if (!$vnpayUrl) {
//                 DB::rollBack();
//                 return response()->json([
//                     'code' => 'error',
//                     'message' => 'Không thể tạo giao dịch VNPay!',
//                 ], 400);
//             }
//         } else {
//             if ($voucherId) {
//                 Voucher::where('id', $voucherId)->increment('numberOfUses');
//             }
//             Cart::where('userId', $user->id)->delete();
//         }

//         DB::commit();

//         return response()->json([
//             'code' => 'success',
//             'message' => $isZaloPay ? 'Chờ thanh toán ZaloPay...' : ($isVNPay ? 'Chuyển hướng VNPay...' : 'Đặt hàng thành công!'),
//             'order_url' => $isZaloPay ? $zalopayResponse['order_url'] : ($isVNPay ? $vnpayUrl : null),
//             'zp_trans_token' => $isZaloPay ? $zalopayResponse['zp_trans_token'] : null,
//             'app_trans_id' => $isZaloPay ? $orderCode : null,
//             'orderId' => $order->id,
//             'discountAmount' => $discountAmount
//         ], 200);
//     } catch (\Exception $e) {
//         DB::rollBack();
//         Log::error("❌ Lỗi khi tạo đơn hàng", ['error' => $e->getMessage()]);
//         return response()->json([
//             'code' => 'error',
//             'message' => 'Lỗi server, vui lòng thử lại!',
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// }

private function createVNPayPayment($orderCode, $amount, $orderId)
{
    $vnp_TmnCode = env('VNP_TMNCODE');
    $vnp_HashSecret = env('VNP_HASH_SECRET');
    $vnp_Url = env('VNP_URL');
    $vnp_Returnurl = env('VNP_RETURN_URL');

    $inputData = [
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $amount * 100,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => request()->ip(),
        "vnp_Locale" => "vn",
        "vnp_OrderInfo" => "Thanh toan don hang #$orderId",
        "vnp_OrderType" => "billpayment",
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $orderCode,
    ];

    ksort($inputData);
    $hashdata = collect($inputData)->map(function ($v, $k) {
        return urlencode($k) . "=" . urlencode($v);
    })->implode('&');

    $query = http_build_query($inputData);
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    return $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $vnpSecureHash;
}

public function handleVNPayReturn(Request $request)
{
    $inputData = $request->all();
    $vnp_HashSecret = env('VNP_HASH_SECRET');

    $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? null;
    unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

    ksort($inputData);
    $hashData = collect($inputData)->map(function ($v, $k) {
        return urlencode($k) . "=" . urlencode($v);
    })->implode('&');

    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

    if ($secureHash === $vnp_SecureHash && $request->vnp_ResponseCode === "00") {
        // Cập nhật đơn hàng
        $order = Order::where('code', $request->vnp_TxnRef)->first();
        if ($order && $order->paymentStatus === 'pending') {
            $order->update([
                'paymentStatus' => 'paid',
                'status' => 'processing'
            ]);

            // Tăng lần dùng voucher
            if ($order->voucherId) {
                Voucher::where('id', $order->voucherId)->increment('numberOfUses');
            }

            // Xóa giỏ hàng
            Cart::where('userId', $order->userId)->delete();
        }

        return redirect('http://localhost:3000/payment-result?code=00');
    }

    return redirect('http://localhost:3000/payment-result?code=' . ($request->vnp_ResponseCode ?? 'error'));
}

    public function createZaloPayPayment($orderCode, $totalPrice)
    {
        $appId = env('ZALOPAY_APP_ID');
        $key1 = env('ZALOPAY_KEY1');
        $endpoint = env('ZALOPAY_ENDPOINT');

        $data = [
            "app_id" => $appId,
            "app_trans_id" => $orderCode,
            "app_user" => "user@example.com",
            "app_time" => round(microtime(true) * 1000),
            "amount" => $totalPrice,
            "item" => json_encode([]),
            "description" => "Thanh toán đơn hàng #" . $orderCode,
            "embed_data" => json_encode([
                // "redirecturl" => "http://localhost:3000/payment-success?orderId=" . $orderCode,
                "redirecturl" => env('ZALOPAY_REDIRECT_URL'),
                "callbackurl" => env('ZALOPAY_CALLBACK_URL'),
                "payment_methods" => ["zalopayapp", "atm", "cc"]
            ]),
            "bank_code" => ""
        ];
        Log::info("⚙️ ZaloPay Payload gửi đi", ['payload' => $data]);

        $dataToHash = implode("|", [
            $data["app_id"],
            $data["app_trans_id"],
            $data["app_user"],
            $data["amount"],
            $data["app_time"],
            $data["embed_data"],
            $data["item"]
        ]);

        $data["mac"] = hash_hmac("sha256", $dataToHash, $key1);

        // $response = Http::asForm()->post($endpoint, $data);

        // return json_decode($response->body(), true);
        $response = Http::asForm()->post($endpoint, $data);
        $result = json_decode($response->body(), true);

        // 🔹 Log phản hồi để kiểm tra
        Log::info("ZaloPay Response:", $result);

        return $result;
    }


    
//     curl -X POST https://7df7-123-21-28-214.ngrok-free.app/api/zalopay/callback \
//   -H "Content-Type: application/json" \
//   -d '{"app_trans_id":"250322_1742636526","mac":"...","data":"{\"status\":1}"}'



    // trạng thái đơn hàng thanh toán zalo
    public function getOrderStatus(Request $request, $orderId)
    {
        $order = Order::find($orderId);

        Log::info("🔍 Kiểm tra trạng thái đơn hàng", ["orderId gửi lên" => $orderId]);

        // ✅ Chỉ tìm theo `code`, vì `orderId` từ ZaloPay là `app_trans_id`
        // $order = DB::table('orders')->where('code', $orderId)->first();

        // if (!$order) {
        //     Log::error("⚠️ Không tìm thấy đơn hàng với mã:", ["orderId" => $orderId]);
        //     return response()->json(["message" => "Không tìm thấy đơn hàng!"], 404);
        // }
        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        Log::info("✅ Đơn hàng tìm thấy", [
            "orderId" => $order->id,
            "paymentStatus" => $order->paymentStatus
        ]);

        return response()->json([
            'paymentStatus' => $order->paymentStatus,
            'status' => $order->status,
            'code' => $order->code,
        ]);
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

    // hủy đơn hàng
    public function cancelOrder($orderId)
    {
        try {
            DB::beginTransaction();
            // Lấy thông tin người dùng đã xác thực
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Người dùng chưa đăng nhập!',
                ], 401);
            }

            // Lấy đơn hàng theo ID và userId
            $order = Order::where('id', $orderId)->where('userId', $user->id)->first();

            if (!$order) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Không tìm thấy đơn hàng!',
                ], 404);
            }

            // Kiểm tra trạng thái đơn hàng, chỉ cho phép hủy khi trạng thái là "pending" hoặc "confirmed"
            if (!in_array($order->status, ['pending', 'confirmed'])) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại!',
                ], 400);
            }
            // Kiểm tra nếu đơn hàng có sử dụng voucher
            if ($order->voucherId) {
                $voucher = Voucher::find($order->voucherId);
                if ($voucher) {
                    // hồi lại số lần sử dụng của voucher 
                    $voucher->decrement('numberOfUses');
                }
            }

            // Cập nhật trạng thái đơn hàng thành "canceled"
            $order->update([
                'status' => 'canceled',
                'updatedAt' => now(),
            ]);

            DB::commit();

            return response()->json([
                'code' => 'success',
                'message' => 'Đơn hàng đã được hủy thành công!',
                'order' => $order
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
