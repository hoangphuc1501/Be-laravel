<?php

namespace App\Http\Controllers;

use App\Models\ProductSpecification;
use Illuminate\Http\Request;

class ProductSpecificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $specifications = ProductSpecification::select(
            'id', 'productId', 'origin', 'stiffness', 'balance_point', 
            'length', 'tension', 'weight', 'racketHandleSize', 
            'frameMaterial', 'shaftMaterial', 'color', 'status', 'deleted'
        )
        ->where('deleted', false)
        ->orderBy('id', 'desc')
        ->paginate($perPage);
        
        return response()->json([
            'code' => 'success',
            'message' => "Hiển thị danh sách thông số kỹ thuật thành công",
            'data' => $specifications,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'productId' => 'required|exists:products,id|unique:productspecifications,productId',
            'origin' => 'nullable|string|max:100',
            'stiffness' => 'nullable|string|max:50',
            'balance_point' => 'nullable|string|max:100',
            'length' => 'nullable|string|max:50',
            'tension' => 'nullable|string|max:100',
            'weight' => 'nullable|string|max:50',
            'racketHandleSize' => 'nullable|string|max:50',
            'frameMaterial' => 'nullable|string|max:100',
            'shaftMaterial' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'status' => 'required|boolean',
        ]);

        $specification = ProductSpecification::create($request->all());

        return response()->json([
            'code' => 'success',
            'message' => 'Thêm thông số kỹ thuật thành công.',
            'data' => $specification
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $specification = ProductSpecification::find($id);
        if (!$specification) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thông số kỹ thuật không tồn tại!'
            ], 400);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Hiển thị thông số kỹ thuật theo id thành công.',
            'data' => $specification
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $specification = ProductSpecification::find($id);
        if (!$specification) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thông số kỹ thuật không tồn tại!'
            ], 404);
        }

        $validatedData = $request->validate([
            'origin' => 'nullable|string|max:100',
            'stiffness' => 'nullable|string|max:50',
            'balance_point' => 'nullable|string|max:100',
            'length' => 'nullable|string|max:50',
            'tension' => 'nullable|string|max:100',
            'weight' => 'nullable|string|max:50',
            'racketHandleSize' => 'nullable|string|max:50',
            'frameMaterial' => 'nullable|string|max:100',
            'shaftMaterial' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'status' => 'required|boolean',
        ]);

        $specification->fill($validatedData);
        if ($specification->isDirty()) {
            $specification->save();
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật thông số kỹ thuật thành công.',
            'data' => $specification
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $specification = ProductSpecification::find($id);
        if (!$specification) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thông số kỹ thuật không tồn tại!'
            ], 400);
        }

        $specification->delete();
        return response()->json([
            'code' => 'success',
            'message' => 'Xóa thông số kỹ thuật thành công.',
        ], 200);
    }

    // xóa mềm
    public function softDelete(string $id)
    {
        $specification = ProductSpecification::where('deleted', false)->find($id);
    
        if (!$specification) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thông số kỹ thuật không tồn tại hoặc đã bị xóa!'
            ], 400);
        }
    
        $specification->update(['deleted' => true]);
    
        return response()->json([
            'code' => 'success',
            'message' => 'Xóa thông số kỹ thuật thành công.',
        ], 200);
    }

    public function restore(string $id)
    {
        $specification = ProductSpecification::where('deleted', true)->find($id);
    
        if (!$specification) {
            return response()->json([
                'code' => 'error',
                'message' => 'Thông số kỹ thuật không tồn tại hoặc chưa bị xóa!'
            ], 400);
        }
    
        $specification->update(['deleted' => false]);
    
        return response()->json([
            'code' => 'success',
            'message' => 'Khôi phục thông số kỹ thuật thành công.',
        ], 200);
    }
}
