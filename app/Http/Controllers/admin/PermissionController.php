<?php

namespace App\Http\Controllers\admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Permission;

class PermissionController extends Controller
{
    //danh sách các quyền
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $permissions = Permission::orderBy('module')->paginate($perPage);
        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách quyền.',
            'data' => $permissions
        ], 200);
    }


    // thông tin của một quyền
    public function show($id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'code' => 'error',
                'message' => 'Quyền không tồn tại.'
            ], 404);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Chi tiết quyền.',
            'data' => $permission
        ], 200);
    }

        // Thêm mới một quyền
        public function store(Request $request)
        {
            // Validate dữ liệu
            $validated = $request->validate([
                'name' => 'required|string|max:50',
                'description' => 'nullable|string',
                'module' => 'required|string|max:50',
                'slug' => 'required|string|max:50',
            ]);
    
            // Tạo mới một quyền
            $permission = Permission::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'module' => $validated['module'],
                'slug' => $validated['slug'],
            ]);
            return response()->json([
                'code' => 'success',
                'message' => 'Tạo quyền thành công.',
                'data' => $permission
            ], 200);
        }

        // Cập nhật một quyền
    public function update(Request $request, $id)
    {
        // Tìm quyền
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'code' => 'error',
                'message' => 'Quyền không tồn tại.'
            ], 404);
        }

        // Validate dữ liệu
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string',
            'module' => 'required|string|max:50',
            'slug' => 'required|string|max:50',
        ]);

        // Cập nhật quyền
        $permission->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'slug' => $validated['slug'],
        ]);

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật quyền thành công.',
            'permission' => $permission
        ]);
    }

    public function destroy($id)
    {
        // Tìm quyền
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'code' => 'error',
                'message' => 'Quyền không tồn tại.'
            ], 404);
        }

        // Xóa quyền
        $permission->delete();

        return response()->json([
            'code' => 'success',
            'message' => 'Xóa quyền thành công.'
        ]);
    }

    public function getAllPermissions()
{
    $permissions = Permission::orderBy('module')->get()->groupBy('module');
    return response()->json([
        'code' => 'success',
        'message' => 'Danh sách quyền.',
        'data' => $permissions
    ]);
}
}
