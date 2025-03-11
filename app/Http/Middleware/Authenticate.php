<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Closure;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // Nếu request là API, trả về JSON thay vì redirect về trang login
        if (!$request->expectsJson()) {
            return route('login');
        }

        return response()->json([
            'code' => 'error',
            'message' => 'Bạn chưa đăng nhập hoặc token không hợp lệ.'
        ], 401);
    }
}

