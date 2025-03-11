<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class JWTMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Nếu request không có token, trả về lỗi ngay
            if (!$request->bearerToken()) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Token không được cung cấp!',
                ], 401);
            }

            // Xác thực token
            $user = JWTAuth::parseToken()->authenticate();

            // Nếu user không hợp lệ
            if (!$user) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Người dùng không hợp lệ!',
                ], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Token đã hết hạn, vui lòng đăng nhập lại!',
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Token không hợp lệ!',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi xác thực token! Token không hợp lệ hoặc không được cung cấp.',
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }

        return $next($request);
    }
}
