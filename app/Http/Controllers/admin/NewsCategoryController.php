<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\NewsCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
class NewsCategoryController extends Controller
{
    // public function index(Request $request)
    // {
    //     $perPage = $request->input('per_page', 10);

    //     $categories = NewsCategory::select(
    //         'newscategories.id',
    //         'newscategories.name',
    //         'newscategories.image',
    //         'newscategories.description',
    //         'newscategories.status',
    //         'newscategories.parentID',
    //         'newscategories.position',
    //         'newscategories.slug',
    //         'parent.name as parentName'
    //     )
    //         ->leftJoin('newscategories as parent', 'newscategories.parentID', '=', 'parent.id')
    //         ->where('newscategories.deleted', false)
    //         ->orderBy('newscategories.position', 'desc')
    //         ->paginate($perPage);

    //     return response()->json([
    //         'code' => 'success',
    //         'message' => 'Danh sách danh mục bài viết.',
    //         'data' => $categories
    //     ]);
    // }

    public function index(Request $request)
    {

        // phân quyền
        $this->authorize('viewAny', NewsCategory::class);

        $perPage = $request->input('per_page', 10);
        $status = $request->input('status');
        $search = $request->input('search');
        $sort = $request->input('sort');

        $query = NewsCategory::select(
            'newscategories.id',
            'newscategories.name',
            'newscategories.image',
            'newscategories.description',
            'newscategories.status',
            'newscategories.parentID',
            'newscategories.position',
            'newscategories.slug',
            'parent.name as parentName'
        )
            ->leftJoin('newscategories as parent', 'newscategories.parentID', '=', 'parent.id')
            ->where('newscategories.deleted', false);

        // Lọc theo trạng thái
        if ($status === 'active') {
            $query->where('newscategories.status', 1);
        } elseif ($status === 'inactive') {
            $query->where('newscategories.status', 0);
        }

        // Tìm kiếm theo tên danh mục
        if (!empty($search)) {
            $query->where('newscategories.name', 'like', '%' . $search . '%');
        }

        // Sắp xếp
        switch ($sort) {
            case 'position-asc':
                $query->orderBy('newscategories.position', 'asc');
                break;
            case 'position-desc':
                $query->orderBy('newscategories.position', 'desc');
                break;
            case 'title-asc':
                $query->orderBy('newscategories.name', 'asc');
                break;
            case 'title-desc':
                $query->orderBy('newscategories.name', 'desc');
                break;
            default:
                $query->orderBy('newscategories.position', 'desc');
                break;
        }

        $categories = $query->paginate($perPage);

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách danh mục bài viết.',
            'data' => $categories
        ]);
    }


    public function store(Request $request)
    {
        // phân quyền
        $this->authorize('create', NewsCategory::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'parentID' => 'nullable|exists:newscategories,id',
            'position' => 'nullable|integer',
        ]);

        $maxPosition = NewsCategory::max('position') ?? 0;
        $slug = generateUniqueSlug($request->name, NewsCategory::class);

        $category = NewsCategory::create([
            'name' => $request->name,
            'image' => $request->image,
            'description' => $request->description,
            'status' => $request->status,
            'parentID' => $request->parentID,
            'position' => $request->position ?? $maxPosition + 1,
            'slug' => $slug,
            'deleted' => false
        ]);

        return response()->json([
            'code' => 'success',
            'message' => 'Tạo danh mục bài viết thành công.',
            'data' => $category->load('parent', 'children')
        ]);
    }

    public function show(string $id)
    {
        $category = NewsCategory::find($id);
        // phân quyền
        $this->authorize('view', $category);
        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục không tồn tại.'
            ], 404);
        }
        return response()->json([
            'code' => 'success',
            'message' => 'Hiển thị danh mục theo id thành công.',
            'data' => $category->only(['id', 'name', 'image', 'description', 'status', 'parentID', 'slug', 'deleted', 'position'])
        ], 200);
    }
    public function update(Request $request, $id)
    {
        // Tìm tin tức theo id
        $category = NewsCategory::find($id);
        // phân quyền
        $this->authorize('update', $category);
        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục không tồn tại.'
            ], 404);
        }

        // Kiểm tra và xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'position' => 'nullable|integer',
            'ParentID' => 'nullable|exists:newscategories,id',
            'status' => 'required|boolean',
        ]);
        $position = $request->has('position') ? $request->position : $category->position;
        $slug = $category->title !== $validated['name'] ? generateUniqueSlug($validated['name'], NewsCategory::class) : $category->slug;

        // Cập nhật thông tin tin tức
        $category->fill([
            'name' => $validated['name'],
            'image' => $validated['image'] ?? $category->image,
            'description' => $validated['description'] ?? $category->description,
            'status' => $validated['status'],
            'parentID' => $request->input('parentID', $category->parentID),
            'position' => $position,
            'slug' => $slug,
        ]);
        if ($category->isDirty()) {
            $category->save();
        }
        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật danh mục thành công.',
            'data' => $category
        ]);
    }

    public function destroy($id)
    {
        $category = NewsCategory::find($id);
        // phân quyền
        $this->authorize('forceDelete', $category);
        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục không tồn tại.'
            ], 404);
        }

        $category->delete();
        return response()->json([
            'code' => 'success',
            'message' => 'Xóa thành công.'
        ]);
    }


    // xóa mềm
    public function softDelete($id)
    {
        $category = NewsCategory::where('deleted', false)->find($id);
        // phân quyền
        $this->authorize('delete', $category);
        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục không tồn tại.'
            ], 404);
        }

        $category->update(['deleted' => true]);

        return response()->json([
            'code' => 'success',
            'message' => 'Xóa thành công.'
        ]);
    }

    // Phục hồi danh mục 
    public function restore($id)
    {
        $category = NewsCategory::where('deleted', true)->find($id);
        // phân quyền
        $this->authorize('restore', $category);
        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục không tồn tại.'
            ], 404);
        }

        $category->update(['deleted' => false]);
        return response()->json([
            'code' => 'success',
            'message' => 'Khôi phục danh mục thành công.'
        ]);
    }

    // danh sách no page
    public function ListCategory()
    {
        $categories = NewsCategory::select('id', 'name')
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderBy('position', 'asc')
            ->get();

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách danh mục bài viết.',
            'data' => $categories
        ]);
    }

    // cập nhật trạng thái
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $category = NewsCategory::find($id);
        // phân quyền
        $this->authorize('update', $category);
        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục bài viết không tồn tại!'
            ], 404);
        }

        $category->status = $request->status;
        $category->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => $category
        ]);
    }

    // thay đổi vị trí
    public function updatePosition(Request $request, $id)
    {
        $request->validate([
            'position' => 'required|integer|min:1',
        ]);

        $category = NewsCategory::where('deleted', false)->find($id);
        // phân quyền
        $this->authorize('update', $category);
        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục không tồn tại.',
            ], 404);
        }

        $category->position = $request->position;
        $category->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật vị trí thành công.',
            'data' => $category
        ]);
    }

    public function trashNewsCategory(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $categories = NewsCategory::select(
            'newscategories.id',
            'newscategories.name',
            'newscategories.image',
            'newscategories.description',
            'newscategories.status',
            'newscategories.parentID',
            'newscategories.position',
            'newscategories.slug',
            'parent.name as parentName'
        )
            ->leftJoin('newscategories as parent', 'newscategories.parentID', '=', 'parent.id')
            ->where('newscategories.deleted', true)
            ->orderBy('newscategories.position', 'desc')
            ->paginate($perPage);

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách danh mục bài viết.',
            'data' => $categories
        ]);
    }

}
