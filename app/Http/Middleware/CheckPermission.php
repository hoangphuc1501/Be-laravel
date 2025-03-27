<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = Auth::guard('client_api')->user();

        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng!'], 401);
        }
        // Nếu user có vai trò Admin, bỏ qua kiểm tra quyền
        $roles = $user->roles()->pluck('name')->toArray();
        if (in_array('Quản trị viên', $roles)) {
            return $next($request);
        }
        // Kiểm tra quyền
        if (!$user->hasPermission($permission)) {
            return response()->json(['message' => 'Bạn không có quyền thực hiện hành động này!'], 403);
        }
        return $next($request);
    }
}
