<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VNPayController extends Controller
{
    public function createPayment(Request $request)
    {
        $vnp_TmnCode = env('VNPAY_TMNCODE');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $vnp_Url = env('VNPAY_URL');
        $vnp_Returnurl = env('VNPAY_RETURN_URL');
        Log::info('VNPay Config:', [
            'VNPAY_TMNCODE' => env('VNPAY_TMNCODE'),
            'VNPAY_HASH_SECRET' => env('VNPAY_HASH_SECRET'),
            'VNPAY_URL' => env('VNPAY_URL'),
            'VNPAY_RETURN_URL' => env('VNPAY_RETURN_URL'),
        ]);
        // Kiểm tra nếu các thông tin này không có
        if (!$vnp_TmnCode || !$vnp_HashSecret || !$vnp_Url || !$vnp_Returnurl) {
            return response()->json(['error' => 'Thiếu thông tin cấu hình VNPay'], 500);
        }
        
        
        // Tạo mã giao dịch duy nhất

        $vnp_TxnRef = 'ORDER_' . time();
        $vnp_OrderInfo = "Thanh toán đơn hàng VNPay";
        $vnp_Amount = $request->amount * 100; // VNPay yêu cầu số tiền tính bằng VND x100
        $vnp_Locale = 'vn';
        // $vnp_BankCode = '';
        $vnp_IpAddr = $request->header('X-Forwarded-For') ?? $request->ip();

        // Danh sách tham số gửi đến VNPay
        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => now()->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => str_replace("+", " ", urlencode($vnp_OrderInfo)),
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_SecureHashType" => "SHA512",
        ];

        // Sắp xếp tham số theo thứ tự alphabet
        ksort($inputData);

        // Tạo chuỗi dữ liệu để hash (đúng chuẩn VNPay)
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($key != "vnp_SecureHash") {
                $hashdata .= $key . "=" . $value . "&";
            }
        }
        $hashdata = rtrim($hashdata, "&");


        // Tạo chữ ký SHA512
        $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $inputData['vnp_SecureHash'] = $vnp_SecureHash;



        // Tạo URL thanh toán
        $query = http_build_query($inputData);
        $paymentUrl = $vnp_Url . '?' . $query;
    

        Log::info('VNPay Hash Data:', ['data' => $hashdata]);
        Log::info('VNPay Secure Hash:', ['hash' => $vnp_SecureHash]);
        Log::info('VNPay Hash Secret:', ['secret' => $vnp_HashSecret]);
        return response()->json(['paymentUrl' => $paymentUrl]);
    }

    public function vnpayReturn(Request $request)
{
    Log::info('VNPay Response Data:', $request->all());

    // Kiểm tra mã phản hồi từ VNPay
    if ($request->vnp_ResponseCode == "00") {
        return response()->json([
            'message' => 'Thanh toán thành công!',
            'order_id' => $request->vnp_TxnRef
        ]);
    } else {
        Log::error('VNPay Payment Failed', [
            'error_code' => $request->vnp_ResponseCode,
            'message' => 'Thanh toán thất bại'
        ]);

        return response()->json([
            'message' => 'Thanh toán thất bại',
            'error_code' => $request->vnp_ResponseCode
        ]);
    }
}

}
