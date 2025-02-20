<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use App\Models\Products;
use App\Models\ProductVariants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    //
    public function store(Request $request)
    {
        // Kiểm tra dữ liệu
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'brandID' => 'required|integer|exists:brands,id',
            'categoriesID' => 'required|integer|exists:productcategories,id',
            'codeProduct' => 'required|string|max:50',
            'position' => 'nullable|integer',
            'description' => 'nullable|string',
            'descriptionPromotion' => 'nullable|string',
            'featured' => 'required|boolean',
            'status' => 'required|boolean',

            'variants' => 'required|array|min:1',
            'variants.*.color' => 'required|string|max:255',
            'variants.*.size' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric',
            'variants.*.discount' => 'nullable|numeric',
            'variants.*.specialPrice' => 'nullable|numeric',
            'variants.*.code' => 'required|string|max:255',
            'variants.*.stock' => 'required|integer',
            'variants.*.status' => 'required|boolean',
            'variants.*.images' => 'nullable|array', 
            'variants.*.images.*' => 'required|string|url'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // tự động tăng cho possiton
        $maxPosition = Products::max('position') ?? 0;
        $newPosition = $maxPosition + 1;

        // Tạo slug
        $slug = generateUniqueSlug($request->title, Products::class);

        // Thêm sản phẩm mới
        $product = Products::create([
            'title' => $request->title,
            'brandID' => $request->brandID,
            'categoriesID' => $request->categoriesID,
            'codeProduct' => $request->codeProduct,
            'position' => $request->position ?? $newPosition,
            'description' => $request->description,
            'descriptionPromotion' => $request->descriptionPromotion,
            'featured' => $request->featured,
            'status' => $request->status,
            'slug' => $slug,
            'deleted' => 0
        ]);

        // Thêm biến thể sản phẩm
        foreach ($request->variants as $variantData) {
            $variant = new ProductVariants([
                'ProductID' => $product->id,
                'color' => $variantData['color'],
                'size' => $variantData['size'],
                'price' => $variantData['price'],
                'discount' => $variantData['discount'] ?? 0,
                'specialPrice' => $variantData['specialPrice'] ?? 0,
                'code' => $variantData['code'],
                'stock' => $variantData['stock'],
                'status' => $variantData['status'],
            ]);
            $variant->save();
              // Nếu có hình ảnh lưu vào bảng 
            if (!empty($variantData['images'])) {
                foreach ($variantData['images'] as $imageUrl) {
                    ProductImage::create([
                        'productVariantID' => $variant->id,
                        'image' => $imageUrl,
                        'imageName' => basename($imageUrl), // Lấy tên file từ URL
                        'status' => 1,
                        'deleted' => 0
                    ]);
                }
            }
            
        }


        return response()->json([
            'code' => 'success',
            'message' => 'Sản phẩm được tạo thành công.',
            'product' => $product->load('variants.images')
        ], 201);
        
    }
}
