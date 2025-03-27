<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;

class RoleController extends Controller
{
    // danh sách vai trò
    public function index(Request $request)
{
    $perPage = $request->input('per_page', 10); 

    $roles = Role::orderBy('createdAt', 'desc')
    ->paginate($perPage);

    return response()->json([
        'code' => 'success',
        'message' => 'Danh sách vai trò.',
        'data' => $roles
    ], 200);
}

    // Lấy thông tin của một vai trò
    public function show($id)
    {
        // $role = Role::find($id);
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return response()->json([
                'code' => 'error',
                'message' => 'Vai trò không tồn tại.'
            ], 404);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Chi tiết vai trò.',
            'data' => $role
        ], 200);
    }

    // Thêm mới vai trò
    public function store(Request $request)
    {
        $permissions = Permission::all()->groupBy('module');


        // Validate dữ liệu
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Tạo mới một vai trò
        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
        ]);
        
        // Lưu quyền cho vai trò mới
        foreach ($validated['permissions'] as $permissionId) {
            RolePermission::create([
                'roleId' => $role->id,
                'permissionId' => $permissionId,
            ]);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Tạo vai trò thành công.',
            'data' => $role
        ], 200);
    }

    // Cập nhật vai trò
    public function update(Request $request, $id)
    {
        // Tìm vai trò
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'code' => 'error',
                'message' => 'Vai trò không tồn tại.'
            ], 404);
        }

        // Validate dữ liệu
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Cập nhật vai trò
        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
        ]);

         // Nếu có quyền được gửi, cập nhật quyền cho vai trò
    if (isset($validated['permissions'])) {
        $role->permissions()->sync($validated['permissions']); 
    }

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật vai trò thành công.',
            'role' => $role
        ]);
    }

    // Xóa vai trò
    public function destroy($id)
    {
        // Tìm vai trò
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'code' => 'error',
                'message' => 'Vai trò không tồn tại.'
            ], 404);
        }

        // Xóa vai trò
        $role->delete();

        return response()->json([
            'code' => 'success',
            'message' => 'Xóa vai trò thành công.'
        ]);
    }
}
