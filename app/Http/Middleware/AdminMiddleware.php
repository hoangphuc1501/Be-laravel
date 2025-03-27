<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserClient;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('client_api')->user();

        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng!'], 401);
        }

        // Lấy danh sách các vai trò của user
        $roles = $user->roles()->pluck('name')->toArray();

        // Chỉ chặn nếu role là "Khách hàng"
        if (in_array('Khách hàng', $roles)) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập!',
                'user_roles' => $roles
            ], 403);
        }

        return $next($request);
    }
}

