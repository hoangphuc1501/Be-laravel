<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use App\Models\ProductVariants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductVariantController extends Controller
{
    //danh sách
    public function index()
    {
        $variants = ProductVariants::with(['images', 'product:id,title'])
        ->where('deleted', false)
        ->get();
        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách biến thể sản phẩm.',
            'data' => $variants
        ], 200);
    }

    // thêm mới
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'ProductID' => 'required|exists:products,id',
            'color' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'specialPrice' => 'nullable|numeric',
            'status' => 'required|boolean',
            'images' => 'nullable|array',
            'images.*' => 'required|string|url'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Tạo mới variant
        $variant = ProductVariants::create([
            'ProductID' => $request->ProductID,
            'color' => $request->color,
            'size' => $request->size,
            'price' => $request->price,
            'discount' => $request->discount ?? 0,
            'specialPrice' => $request->specialPrice ?? 0,
            'status' => $request->status
        ]);

        if ($request->has('images')) {
            foreach ($request->images as $imageUrl) {
                ProductImage::create([
                    'productVariantID' => $variant->id,
                    'image' => $imageUrl,
                    'imageName' => basename($imageUrl),
                    'status' => 1,
                    'deleted' => 0
                ]);
            }
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Biến thể được tạo thành công.',
            'variant' => $variant->load('images')
        ], 201);
    }


    // cập nhật biến thểpublic 

    function update(Request $request, $id)
    {
        // Tìm biến thể theo ID
        // $variant = ProductVariants::findOrFail($id);
        $variant = ProductVariants::find($id);
        if (!$variant) {
            return response()->json([
                'code' => 'error',
                'message' => 'Biến thể không tồn tại.'
            ], 404);
        }

        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'color' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'specialPrice' => 'nullable|numeric',
            'status' => 'required|boolean',
            'images' => 'nullable|array',
            'images.*' => 'required|string|url'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cập nhật thông tin biến thể
        $variant->update([
            'color' => $request->color,
            'size' => $request->size,
            'price' => $request->price,
            'discount' => $request->discount ?? 0,
            'specialPrice' => $request->specialPrice ?? 0,
            'status' => $request->status
        ]);

        // Nếu có hình ảnh được gửi lên, xóa hình ảnh cũ và thêm hình ảnh mới
        if ($request->has('images')) {
            // Xóa hình ảnh cũ
            $variant->images()->delete();

            // Thêm hình ảnh mới
            foreach ($request->images as $imageUrl) {
                ProductImage::create([
                    'productVariantID' => $variant->id,
                    'image' => $imageUrl,
                    'imageName' => basename($imageUrl),
                    'status' => 1,
                    'deleted' => 0
                ]);
            }
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Biến thể được cập nhật thành công.',
            'variant' => $variant->load('images')
        ], 200);
    }

    public function show($id)
    {     
        // $variant = ProductVariants::with('images')->findOrFail($id);

        $variant = ProductVariants::with('images')
        ->where('id', $id)
        ->where('deleted', false)
        ->first();

    if (!$variant) {
        return response()->json([
            'code' => 'error',
            'message' => 'Biến thể không tồn tại.'
        ], 404);
    }

        if (!$variant) {
            return response()->json([
                'code' => 'error',
                'message' => 'Biến thể không tồn tại.'
            ], 404);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Hiển thị biến thể thành công.',
            'variant' => $variant
        ], 200);
    }

    // xóa cứng
    public function destroy($id)
{

    // $variant = ProductVariants::findOrFail($id);

    $variant = ProductVariants::with('images')
        ->where('id', $id)
        ->where('deleted', false)
        ->first();

    if (!$variant) {
        return response()->json([
            'code' => 'error',
            'message' => 'Biến thể không tồn tại.'
        ], 404);
    };

    if (!$variant) {
        return response()->json([
            'code' => 'error',
            'message' => 'Biến thể không tồn tại.'
        ], 404);
    }

    $variant->delete();

    return response()->json([
        'code'    => 'success',
        'message' => 'Biến thể đã được xóa thành công.'
    ], 200);
}

// xóa mềm
public function softDelete($id)
{
    // $variant = ProductVariants::findOrFail($id);
    $variant = ProductVariants::with('images')
        ->where('id', $id)
        ->where('deleted', false)
        ->first();

    if (!$variant) {
        return response()->json([
            'code' => 'error',
            'message' => 'Biến thể không tồn tại hoặc đã bị xóa.'
        ], 404);
    }

    $variant->update(['deleted' => true]);

    return response()->json([
        'code'    => 'success',
        'message' => 'Biến thể đã được xóa thành công.'
    ], 200);
}

// khôi phục
public function restore($id)
{

    // $variant = ProductVariants::findOrFail($id);
    $variant = ProductVariants::with('images')
        ->where('id', $id)
        ->where('deleted', true)
        ->first();

    if (!$variant) {
        return response()->json([
            'code' => 'error',
            'message' => 'Biến thể không tồn tại hoặc chưa bị xóa'
        ], 404);
    }

    $variant->update(['deleted' => false]);

    return response()->json([
        'code'    => 'success',
        'message' => 'Biến thể đã được khôi phục thành công.'
    ], 200);
}

}
