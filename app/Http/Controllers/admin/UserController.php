<?php

namespace App\Http\Controllers\admin;

use App\Models\UserClient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    // Lấy danh sách user
    // public function index(Request $request)
    // {
    //     $perPage = $request->input('per_page', 10);

    //     $users = UserClient::select('id', 'fullname', 'email', 'status', 'position', 'deleted', 'phone')
    //         ->where('deleted', false)
    //         ->orderBy('position', 'desc')
    //         ->with([
    //             'roles' => function ($query) {
    //                 $query->select('roles.id', 'roles.name');
    //             }
    //         ])
    //         ->paginate($perPage);

    //     return response()->json([
    //         'code' => 'success',
    //         'message' => "Hiển thị danh sách khách hàng thành công.",
    //         'data' => $users,
    //     ], 200);
    // }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $status = $request->input('status');
        $search = $request->input('search');
        $roleId = $request->input('roleId');
        $sort = $request->input('sort');

        $query = UserClient::select('id', 'fullname', 'email', 'status', 'position', 'deleted', 'phone')
            ->where('deleted', false)
            ->with([
                'roles' => function ($query) {
                    $query->select('roles.id', 'roles.name');
                }
            ]);

        // Lọc theo trạng thái
        if ($status === 'active') {
            $query->where('status', 1);
        } elseif ($status === 'inactive') {
            $query->where('status', 0);
        }

        // Tìm kiếm theo tên, email, hoặc số điện thoại
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        // Lọc theo vai trò
        if (!empty($roleId)) {
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        // Sắp xếp
        switch ($sort) {
            case 'position-asc':
                $query->orderBy('position', 'asc');
                break;
            case 'position-desc':
                $query->orderBy('position', 'desc');
                break;
            case 'name-asc':
                $query->orderBy('fullname', 'asc');
                break;
            case 'name-desc':
                $query->orderBy('fullname', 'desc');
                break;
            default:
                $query->orderBy('position', 'desc');
                break;
        }

        $users = $query->paginate($perPage);

        return response()->json([
            'code' => 'success',
            'message' => "Hiển thị danh sách khách hàng thành công.",
            'data' => $users,
        ], 200);
    }



    // Lấy chi tiết user theo ID
    public function show($id)
    {
        $user = UserClient::with('roles')->find($id);

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
            'user' => [
                'id' => $user->id,
                'fullname' => $user->fullname,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'address' => $user->address,
                'birthday' => $user->birthday,
                'gender' => $user->gender,
                'createdAt' => $user->createdAt,
                'updatedAt' => $user->updatedAt,
                'position' => $user->position,
                'roles' => $user->roles->map(fn($role) => ['id' => $role->id, 'name' => $role->name])
            ]

        ], 200);
    }

    public function store(Request $request)
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
            'roles' => 'array', // Mảng các vai trò
            'roles.*' => 'exists:roles,id' // Mỗi role phải tồn tại trong bảng roles
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

            // Nếu request có roles, thêm vai trò cho user
            if ($request->has('roles')) {
                $user->roles()->attach($request->roles);
            }

            DB::commit();

            return response()->json([
                'code' => 'success',
                'message' => 'Đăng ký thành công!',
                'user' => [
                    'fullname' => $user->fullname,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'roles' => $user->roles()->pluck('name')
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

    public function update(Request $request, $id)
    {
        // Kiểm tra xem người dùng có tồn tại không
        $user = UserClient::find($id);
        if (!$user) {
            return response()->json([
                'code' => 'error',
                'message' => 'Người dùng không tồn tại!'
            ], 404);
        }

        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'fullname' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'phone' => 'sometimes|required|string|max:15',
            'password' => 'nullable|string|min:6',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Cập nhật thông tin người dùng
            $user->update([
                'email' => $request->email ?? $user->email,
            ]);

            // Nếu có danh sách roles, cập nhật lại vai trò
            if ($request->has('roles')) {
                $user->roles()->sync($request->roles);
            }

            DB::commit();

            return response()->json([
                'code' => 'success',
                'message' => 'Cập nhật người dùng thành công!',
                'user' => [
                    'email' => $user->email,
                    'roles' => $user->roles()->pluck('name')
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


    // xóa vĩnh viễn user
    public function destroy($id)
    {
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
    public function softDelete($id)
    {
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
    public function restore($id)
    {
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

    // cập nhật trạng thái
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $user = UserClient::find($id);
        if (!$user) {
            return response()->json([
                'code' => 'error',
                'message' => 'Người dùng không tồn tại!'
            ], 404);
        }

        $user->status = $request->status;
        $user->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => $user
        ]);
    }

    // thay đổi vị trí
    public function updatePosition(Request $request, $id)
    {
        $request->validate([
            'position' => 'required|integer|min:1',
        ]);

        $user = UserClient::where('deleted', false)->find($id);

        if (!$user) {
            return response()->json([
                'code' => 'error',
                'message' => 'Người dùng không tồn tại!'
            ], 404);
        }

        $user->position = $request->position;
        $user->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật vị trí thành công.',
            'data' => $user
        ]);
    }




}
