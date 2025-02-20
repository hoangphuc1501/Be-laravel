<?php

namespace App\Http\Controllers;

use App\Models\UserClient;
use Illuminate\Http\Request;

class UserController extends Controller
{
    
    // Lấy danh sách user
    public function index(Request $request)
    {   
        $perPage = $request->input('per_page', 10);

        $users = UserClient::select('id', 'fullname', 'email', 'status', 'position', 'deleted', 'phone')
        ->where('deleted', false)
        ->orderBy('position', 'desc')
        ->paginate($perPage);
        // ->get();
        
    return response()->json([
        'code' => 'success',
        'message' => "Hiển thị danh khách hàng thành công.",
        'data' => $users,
    ], 200);
    }

     // Lấy chi tiết user theo ID
    public function show($id) {
        $user = UserClient::find($id);

        if (!$user || $user->deleted) {
            return response()->json([
                'code' => 'error',
                'message' => "user không tồn tại!"
            ], 400);
        }
        // return response()->json($user, 200);
        return response()->json([
            'code' => 'success',
            'message' => "Hiển thị user theo id thành công.",
            $user
        ], 200);
    }

    // xóa vĩnh viễn user
    public function destroy($id) {
        $user = UserClient::find($id);

        if (!$user) {
            return response()->json([
                'code' => 'error',
                'message' => "User không tồn tại!"
            ], 400);
        }
    
        $user->delete();
    
        return response()->json([
            'code' => 'success',
            'message' => "User đã được xóa vĩnh viễn."
        ], 200);
    }

    // xóa mềm user
    public function softDelete($id) {
        $user = UserClient::find($id);
    
        if (!$user || $user->deleted) {
            return response()->json([
                'code' => 'error',
                'message' => "User không tồn tại hoặc đã bị xóa!"
            ], 400);
        }
    
        $user->update([
            'deleted' => true,
            'deletedAt' => now()
        ]);
    
        return response()->json([
            'code' => 'success',
            'message' => "User đã được xóa."
        ], 200);
    }

    // khôi phục user
    public function restore($id) {
        $user = UserClient::find($id);
    
        if (!$user || !$user->deleted) {
            return response()->json([
                'code' => 'error',
                'message' => "User không tồn tại hoặc chưa bị xóa!"
            ], 400);
        }
    
        $user->update([
            'deleted' => 0,
            'deletedAt' => null
        ]);
    
        return response()->json([
            'code' => 'success',
            'message' => "User đã được khôi phục."
        ], 200);
    }
}
