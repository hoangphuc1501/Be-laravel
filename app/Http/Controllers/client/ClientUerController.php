<?php

namespace App\Http\Controllers\client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\DeleteOtpAfterDelay;
use App\Mail\OtpMail;
use App\Models\Role;
use App\Models\UserClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;

class ClientUerController extends Controller
{
    public function register(Request $request)
    {

        // Kiểm tra xem email đã tồn tại chưa
        if (UserClient::where('email', $request->email)->exists()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Email đã tồn tại, vui lòng sử dụng email khác!'
            ], 400);
        }
        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        DB::beginTransaction();
        try {
            // Lấy position lớn nhất hiện có
            $maxPosition = UserClient::max('position') ?? 0;
            $newPosition = $maxPosition + 1;
    
            // Tạo người dùng mới
            $user = UserClient::create([
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'status' => 1,
                'deleted' => 0,
                'position' => $newPosition
            ]);
    
            // Kiểm tra role "Khách hàng" có tồn tại không, nếu chưa thì tạo mới
            $customerRole = Role::firstOrCreate(['name' => 'Khách hàng']);
    
            // Gán role "Khách hàng" cho user mới
            $user->roles()->attach($customerRole->id);
    
            DB::commit();
    
            return response()->json([
                'code' => 'success',
                'message' => 'Đăng ký thành công!',
                'user' => [
                    'fullname' => $user->fullname,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'roles' => $user->roles()->pluck('name') // Trả về vai trò của user
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'code' => 'error',
                'message' => 'Đã có lỗi xảy ra',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // đăng nhập
    public function login(Request $request)
    {
        // Validate dữ liệu đầu vào
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Tìm user theo email
        $user = UserClient::where('email', $credentials['email'])
            ->where('status', 1)
            ->where('deleted', 0)
            ->first();

        if (!$user) {
            return response()->json([
                'code' => 'error',
                'message' => 'Người dùng không tồn tại',
            ], 400);
        }

        // Kiểm tra tài khoản có bị khóa không
        if ($user->status === 0) {
            return response()->json([
                'code' => 'error',
                'message' => 'Tài khoản đã bị khóa!',
            ], 400);
        }

        // Kiểm tra mật khẩu
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'code' => 'error',
                'message' => 'Mật khẩu không chính xác',
            ], 400);
        }

        // Tạo JWT token
        $token = Auth::guard('client_api')->attempt($credentials);
        if (!$token) {
            return response()->json([
                'code' => 'error',
                'message' => 'Đăng nhập thất bại!',
            ], 400);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Đăng nhập thành công!',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'fullname' => $user->fullname,
                'email' => $user->email
            ],
        ], 200);
    }


    // quên mật khẩu
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // Tìm user theo email
        $user = UserClient::where('email', $request->email)
            ->where('deleted', 0)
            ->where('status', 1)
            ->first();

        if (!$user) {
            return response()->json([
                'code' => 'error',
                'message' => 'Email không tồn tại hoặc tài khoản đã bị khóa.',
            ], 400);
        }

        // Kiểm tra xem user đã yêu cầu OTP trước đó chưa
        if ($user->otpExpireAt && Carbon::parse($user->otpExpireAt)->isFuture()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Bạn đã yêu cầu OTP trước đó. Vui lòng thử lại sau 5 phút!',
            ], 400);
        }

        // Tạo OTP ngẫu nhiên
        $otp = random_int(100000, 999999);
        $hashedOtp = Hash::make($otp);

        // Lưu OTP vào database
        $user->update([
            'otp' => $hashedOtp,
            'otpExpireAt' => now()->addMinutes(5) // OTP hết hạn sau 5 phút
        ]);

        // Tự động xóa OTP sau 5 phút
        DeleteOtpAfterDelay::dispatch($user->id)->delay(now()->addMinutes(5));

        // Gửi OTP qua email
        $subject = "Xác thực mã OTP";
        $text = "Mã xác thực của bạn là <b>{$otp}</b>. Mã OTP có hiệu lực trong vòng 5 phút, vui lòng không cung cấp mã OTP cho bất kỳ ai.";
        Mail::to($user->email)->send(new \App\Mail\OtpMail($subject, $text));

        return response()->json([
            'code' => 'success',
            'message' => 'Gửi mã OTP thành công.',
        ], 200);
    }

    // xác thực mã otp
    public function verifyOtp(Request $request)
    {
        // Kiểm tra input đầu vào
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
        ]);

        // Tìm user theo email
        $user = UserClient::where('email', $request->email)
            ->where('deleted', 0)
            ->where('status', 1)
            ->first();

        if (!$user) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không tìm thấy tài khoản!',
            ], 400);
        }

        // Kiểm tra xem OTP có tồn tại không
        if (!$user->otp || !$user->otpExpireAt) {
            return response()->json([
                'code' => 'error',
                'message' => 'Mã OTP không hợp lệ!',
            ], 400);
        }

        // Kiểm tra OTP đã hết hạn chưa
        if (Carbon::parse($user->otpExpireAt)->isPast()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Mã OTP đã hết hạn!',
            ], 400);
        }

        // So sánh OTP nhập vào với OTP đã mã hóa trong database
        if (!Hash::check($request->otp, $user->otp)) {
            return response()->json([
                'code' => 'error',
                'message' => 'Mã OTP không chính xác!',
            ], 400);
        }

        // Xóa OTP sau khi xác minh thành công (tránh lạm dụng OTP cũ)
        $user->update([
            'otp' => null,
            'otpExpireAt' => null
        ]);

        // Tạo mật khẩu mới ngẫu nhiên
        $newPassword = Str::random(10);
        $hashedNewPassword = Hash::make($newPassword);

        // Cập nhật mật khẩu mới vào database
        $user->update([
            'password' => $hashedNewPassword,
        ]);

        // Gửi email mật khẩu mới
        $subject = "Mật khẩu mới";
        $text = "Mật khẩu mới của bạn là <b>{$newPassword}</b>. Vui lòng không chia sẻ với bất kỳ ai.";

        Mail::to($user->email)->send(new OtpMail($subject, $text));

        return response()->json([
            'code' => 'success',
            'message' => 'Xác thực OTP thành công, mật khẩu mới đã được gửi qua email của bạn.'
        ], 200);
    }

    // đổi mật khẩu 

    public function changePassword(Request $request)
    {
        // Kiểm tra input đầu vào
        $request->validate([
            'oldPassword' => 'required|string|min:6',
            'newPassword' => 'required|string|min:6|different:oldPassword',
        ]);

        try {
            // Lấy user từ token (Middleware `jwt.auth` sẽ xác thực trước)
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Thông tin người dùng không hợp lệ!',
                ], 400);
            }

            // Kiểm tra mật khẩu cũ
            if (!Hash::check($request->oldPassword, $user->password)) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Mật khẩu cũ không chính xác!',
                ], 400);
            }

            // Cập nhật mật khẩu mới
            $user->password = Hash::make($request->newPassword);
            $user->save();

            // Đăng xuất khỏi tất cả các phiên để bảo mật
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'code' => 'success',
                'message' => 'Đổi mật khẩu thành công! Bạn cần đăng nhập lại.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // hiển thị thông tin user
    public function profile(Request $request)
    {
        try {
            $user = UserClient::select(
                'id',
                'fullname',
                'email',
                'address',
                'phone',
                'image',
                'birthday',
                'gender',
                'createdAt'
            )
                ->where('id', Auth::id())
                ->where('deleted', 0)
                ->where('status', 1)
                ->first();

            if (!$user) {
                return response()->json([
                    'error' => 'Người dùng không tồn tại!'
                ], 404);
            }

            return response()->json([
                'code' => 'success',
                'message' => 'Hiển thị thông tin thành công!',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi lấy thông tin người dùng!'
            ], 500);
        }
    }

    // cập nhật thông tin
    public function updateProfile(Request $request)
    {
        // Kiểm tra nếu user chưa đăng nhập
        $user = Auth::guard('client_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Token không hợp lệ hoặc không có quyền truy cập!'], 401);
        }

        // Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'fullname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
            'image' => 'nullable|string',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ!',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Cập nhật thông tin người dùng
            $user->fill($request->only(['fullname', 'email', 'address', 'phone', 'image', 'birthday', 'gender']));
            $user->save();
            return response()->json([
                'code' => 'success',
                'message' => 'Cập nhật thông tin người dùng thành công!',
                'user' => $user->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi cập nhật thông tin người dùng!',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function loginAdmin(Request $request)
{
    // Validate dữ liệu đầu vào
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ]);

    // Tìm user theo email
    $user = UserClient::where('email', $credentials['email'])
        ->where('status', 1)
        ->where('deleted', 0)
        ->first();

    if (!$user) {
        return response()->json([
            'code' => 'error',
            'message' => 'Người dùng không tồn tại',
        ], 400);
    }

    // Kiểm tra tài khoản có bị khóa không
    if ($user->status === 0) {
        return response()->json([
            'code' => 'error',
            'message' => 'Tài khoản đã bị khóa!',
        ], 400);
    }
    
    if (!Hash::check($credentials['password'], $user->password)) {
        return response()->json([
            'code' => 'error',
            'message' => 'Mật khẩu không chính xác',
        ], 400);
    }
    // Lấy vai trò của user
    $roles = $user->roles()->pluck('name')->toArray(); // Lấy danh sách vai trò

    // Nếu user có vai trò "Khách hàng", không cho vào admin
    if (in_array('Khách hàng', $roles)) {
        return response()->json([
            'code' => 'error',
            'message' => 'Bạn không có quyền truy cập vào hệ thống admin!',
        ], 403);
    }

    // Tạo JWT token
    $token = Auth::guard('client_api')->attempt($credentials);
    if (!$token) {
        return response()->json([
            'code' => 'error',
            'message' => 'Đăng nhập thất bại!',
        ], 400);
    }

    return response()->json([
        'code' => 'success',
        'message' => 'Đăng nhập thành công!',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'roles' => $roles,
            'permissions' => $user->permission_slugs,
        ],
    ], 200);
}
}
