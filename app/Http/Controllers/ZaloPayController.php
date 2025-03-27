<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZaloPayController extends Controller
{
    // public function createPayment(Request $request)
    // {
    //     // $appId = "2554"; // App ID của bạn
    //     // $key1 = "sdngKKJmqEMzvh5QQcdD2A9XBSKUNaYn"; // Key1 từ ZaloPay
    //     // $endpoint = "https://sb-openapi.zalopay.vn/v2/create"; // API URL
    //     $appId = env('ZALOPAY_APP_ID');
    // $key1 = env('ZALOPAY_KEY1');
    // $endpoint = env('ZALOPAY_ENDPOINT');
    
    //     $orderId = time(); // Mã đơn hàng duy nhất
    //     $amount = $request->amount; // Giá trị đơn hàng
    //     $description = "Thanh toán đơn hàng #" . $orderId;
    
    //     // Dữ liệu cần gửi
    //     $data = [
    //         "app_id" => $appId,
    //         "app_trans_id" => date("ymd") . "_" . $orderId,
    //         "app_user" => "user@example.com",
    //         "app_time" => round(microtime(true) * 1000),
    //         "amount" => $amount,
    //         "item" => "[]", // Định dạng JSON chuẩn
    //         "description" => $description,
    //         "embed_data" => "{}",
    //         "bank_code" => "zalopayapp"
    //     ];
    
    //     // Tạo mã bảo mật `mac`
    //     $dataToHash = implode("|", [
    //         $data["app_id"],
    //         $data["app_trans_id"],
    //         $data["app_user"],
    //         $data["amount"],
    //         $data["app_time"],
    //         $data["embed_data"],
    //         $data["item"]
    //     ]);
        
    //     $data["mac"] = hash_hmac("sha256", $dataToHash, $key1);
    
    //     // Ghi log để kiểm tra dữ liệu gửi đi
    //     Log::info('ZaloPay Request Data:', $data);
    //     Log::info('ZaloPay Hash MAC:', ['mac' => $data["mac"]]);
    
    //     // Gửi request đến ZaloPay với `application/x-www-form-urlencoded`
    //     $response = Http::asForm()->post($endpoint, $data);
    
    //     // Nhận phản hồi từ ZaloPay
    //     $body = $response->body();
    //     Log::info('ZaloPay API Response:', json_decode($body, true));
    
    //     return response()->json([
    //         "zalopay_response" => json_decode($body, true),
    //         "status" => $response->status(),
    //         "error" => $response->failed() ? $response->body() : null
    //     ]);
    // }
    

//     public function createZaloPayPayment($order)
// {
//     $appId = env('ZALOPAY_APP_ID');
//     $key1 = env('ZALOPAY_KEY1');
//     $endpoint = env('ZALOPAY_ENDPOINT');

//     $data = [
//         "app_id" => $appId,
//         "app_trans_id" => date("ymd") . "_" . $order->id,
//         "app_user" => "user@example.com",
//         "app_time" => round(microtime(true) * 1000),
//         "amount" => $order->totalPrice,
//         "item" => json_encode([]),
//         "description" => "Thanh toán đơn hàng #" . $order->id,
//         "embed_data" => json_encode(["redirecturl" => "http://localhost:3000/payment-success"]),
//         "bank_code" => "zalopayapp"
//     ];

//     $dataToHash = implode("|", [
//         $data["app_id"],
//         $data["app_trans_id"],
//         $data["app_user"],
//         $data["amount"],
//         $data["app_time"],
//         $data["embed_data"],
//         $data["item"]
//     ]);
    
//     $data["mac"] = hash_hmac("sha256", $dataToHash, $key1);

//     $response = Http::asForm()->post($endpoint, $data);

//     return json_decode($response->body(), true);
// }

// public function simulatePayment(Request $request)
// {
//     $appId = "2554";
//     $mTransId = $request->m_trans_id; // Thay zp_trans_token bằng m_trans_id

//     // 🔹 Kiểm tra nếu thiếu `m_trans_id`
//     if (!$mTransId) {
//         Log::error("❌ Thiếu m_trans_id");
//         return response()->json(["error" => "Thiếu m_trans_id"], 400);
//     }

//     // ✅ Log dữ liệu gửi đi
//     Log::info("📢 Gửi simulate request", [
//         "url" => "https://sb-openapi.zalopay.vn/v2/transaction/simulate",
//         "app_id" => $appId,
//         "m_trans_id" => $mTransId
//     ]);

//     // 🔹 Gửi request đúng URL với `m_trans_id`
//     $response = Http::asForm()->post("https://sb-openapi.zalopay.vn/v2/transaction/simulate", [
//         "app_id" => $appId,
//         "m_trans_id" => $mTransId
//     ]);

//     // 🔹 Kiểm tra HTTP response
//     if (!$response->successful()) {
//         Log::error("❌ Lỗi kết nối API ZaloPay", ['status' => $response->status(), 'body' => $response->body()]);
//         return response()->json([
//             "error" => "Lỗi kết nối API ZaloPay",
//             "status_code" => $response->status(),
//             "body" => $response->body(),
//         ], 500);
//     }

//     $result = json_decode($response->body(), true);

//     // 🔹 Kiểm tra JSON response từ ZaloPay
//     if (!is_array($result) || !isset($result['return_code'])) {
//         Log::error("⚠️ Phản hồi từ simulate không hợp lệ", ['response' => $response->body()]);
//         return response()->json([
//             "error" => "Phản hồi từ ZaloPay không hợp lệ",
//             "raw_response" => $response->body()
//         ], 400);
//     }

//     // 🔹 Nếu giao dịch thành công
//     if ($result['return_code'] == 1) {
//         Log::info("✅ Thanh toán ZaloPay thành công", ['order_id' => $request->order_id]);
//         return response()->json([
//             "message" => "Thanh toán thành công!",
//             "order_id" => $request->order_id,
//             "status" => "paid"
//         ]);
//     }

//     // 🔹 Nếu giao dịch thất bại
//     Log::warning("❌ Thanh toán thất bại", ['error' => $result['return_message'] ?? "Không rõ nguyên nhân"]);
//     return response()->json([
//         "message" => "Thanh toán thất bại!",
//         "error" => $result['return_message'] ?? "Không rõ nguyên nhân"
//     ], 400);
// }

public function callback(Request $request)
    {
        Log::info('📢 ZaloPay Callback Data:', $request->all());

        $data = $request->all();

        if (!isset($data['app_trans_id']) || !isset($data['mac'])) {
            Log::error("⚠️ Thiếu app_trans_id hoặc mac trong callback");
            return response()->json(["message" => "Dữ liệu callback không hợp lệ"], 400);
        }

        $orderCode = $data['app_trans_id'];
        $order = Order::where('code', $orderCode)->first();

        if (!$order) {
            Log::error("⚠️ Không tìm thấy đơn hàng với mã: " . $orderCode);
            return response()->json(["message" => "Đơn hàng không tồn tại"], 400);
        }

        $key2 = env('ZALOPAY_KEY2');
        $dataStr = $data['data'];
        $mac = hash_hmac("sha256", $dataStr, $key2);

        if ($mac !== $data['mac']) {
            Log::warning("⚠️ MAC không hợp lệ!", ['expected_mac' => $mac, 'received_mac' => $data['mac']]);
            return response()->json(["message" => "Xác thực thất bại"], 400);
        }

        $dataObj = json_decode($dataStr, true);

        if (!isset($dataObj['status'])) {
            Log::error("⚠️ Dữ liệu callback thiếu trường 'status'");
            return response()->json(["message" => "Thiếu trạng thái giao dịch"], 400);
        }

        if ($dataObj['status'] == 1) {
            if ($order->paymentStatus !== 'paid') {
                $order->update(['paymentStatus' => 'paid', 'status' => 'pending']);
                Cart::where('userId', $order->userId)->delete();
                if ($order->voucherId) {
                    Voucher::where('id', $order->voucherId)->increment('numberOfUses');
                }
                Log::info("✅ Giao dịch thành công. Cập nhật đơn hàng: " . $orderCode);
            }

            return response()->json(["return_code" => 1, "return_message" => "Thanh toán thành công"], 200);
        } else {
            Log::warning("⚠️ Giao dịch thất bại từ ZaloPay", ['status' => $dataObj['status']]);
            return response()->json(["return_code" => 2, "return_message" => "Giao dịch thất bại"], 400);
        }


    }









}
