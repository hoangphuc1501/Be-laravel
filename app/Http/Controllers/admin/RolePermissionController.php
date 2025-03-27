<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UserClient;
use App\Models\UserRole;

class RolePermissionController extends Controller
{



    // Phân quyền cho Role
    public function assignPermissionsToRole(Request $request, $roleId)
    {
        // Validate dữ liệu
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Kiểm tra vai trò có tồn tại không
        $role = Role::find($roleId);
        if (!$role) {
            return response()->json([
                'code' => 'error',
                'message' => 'Vai trò không tồn tại.'
            ], 404);
        }

        // Xóa các quyền hiện tại của vai trò
        RolePermission::where('roleId', $role->id)->delete();

        // Thêm các quyền mới vào vai trò
        foreach ($validated['permissions'] as $permissionId) {
            RolePermission::create([
                'roleId' => $role->id,
                'permissionId' => $permissionId,
            ]);
        }

        // return response()->json(['message' => 'Permissions assigned successfully']);
        return response()->json([
            'code' => 'success',
            'message' => 'Thêm quyền cho vai trò thành công.',
        ], 200);
    }

    public function assignRoleToUser(Request $request, $userId)
    {
        // Validate dữ liệu
        $validated = $request->validate([
            'roleId' => 'required|exists:roles,id', // Kiểm tra roleId có tồn tại trong bảng roles
        ]);

        // Kiểm tra người dùng có tồn tại không
        $user = UserClient::find($userId);
        if (!$user) {
            return response()->json([
                'code' => 'error',
                'message' => 'Người dùng không tồn tại.'
            ], 404);
        }

        // Kiểm tra vai trò có tồn tại không
        $role = Role::find($validated['roleId']);
        if (!$role) {
            return response()->json([
                'code' => 'error',
                'message' => 'Vai trò không tồn tại.'
            ], 404);
        }
        // Kiểm tra xem người dùng đã có vai trò này chưa
        $existingRole = UserRole::where('userId', $user->id)
            ->where('roleId', $role->id)
            ->first();

        if ($existingRole) {
            return response()->json([
                'code' => 'error',
                'message' => 'Người dùng đã có vai trò này.'
            ], 400);
        }
        // Gán vai trò cho người dùng
        UserRole::create([
            'userId' => $user->id,
            'roleId' => $role->id,
        ]);

        return response()->json([
            'code' => 'success',
            'message' => 'Thêm vai trò cho người dùng thành công.',
        ], 200);
    }
}
