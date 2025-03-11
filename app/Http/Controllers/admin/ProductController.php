<?php

namespace App\Http\Controllers\admin;

use App\Models\ProductImage;
use App\Models\Products;
use App\Models\ProductVariants;
use App\Models\VariationOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;


class ProductController extends Controller
{
    public function index()
{
    $products = Products::select('id', 'title', 'codeProduct', 'brandID', 'categoriesID', 'slug', 'position', 'description', 'featured', 'descriptionPromotion', 'status')
    ->where('deleted', false)
    ->orderBy('position', 'desc')
    ->with([
        'brand:id,name', // Lấy thông tin thương hiệu
        'category:id,name', // Lấy thông tin danh mục
        'variants' => function ($query) {
            $query->select('id', 'ProductID', 'price', 'discount', 'specialPrice', 'status')
                ->with([
                    'images:id,productVariantID,image'
                ]);
        }
    ])
        ->paginate(10);
        if ($products->isEmpty()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không có sản phẩm nào.',
                'data' => []
            ], 200);
        }
    return response()->json([
        'code' => 'success',
        'message' => 'Danh sách sản phẩm.',
        'data' => $products
    ], 200);
}


    // public function store(Request $request)
    // {
    //     // Kiểm tra dữ liệu
    //     $validator = Validator::make($request->all(), [
    //         'title' => 'required|string|max:255',
    //         'brandID' => 'required|integer|exists:brands,id',
    //         'categoriesID' => 'required|integer|exists:productcategories,id',
    //         'position' => 'nullable|integer',
    //         'description' => 'nullable|string',
    //         'descriptionPromotion' => 'nullable|string',
    //         'featured' => 'required|boolean',
    //         'status' => 'required|boolean',

    //         'variants' => 'required|array|min:1',
    //         'variants.*.color' => 'required|string|max:255',
    //         'variants.*.size' => 'required|string|max:255',
    //         'variants.*.price' => 'required|numeric',
    //         'variants.*.discount' => 'nullable|numeric',
    //         'variants.*.specialPrice' => 'nullable|numeric',
    //         'variants.*.status' => 'required|boolean',
    //         'variants.*.images' => 'nullable|array', 
    //         'variants.*.images.*' => 'required|string|url'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     // tự động tăng cho possiton
    //     $maxPosition = Products::max('position') ?? 0;
    //     $newPosition = $maxPosition + 1;

    //     // Tạo slug
    //     $slug = generateUniqueSlug($request->title, Products::class);

    //     // Thêm sản phẩm mới
    //     $product = Products::create([
    //         'title' => $request->title,
    //         'brandID' => $request->brandID,
    //         'categoriesID' => $request->categoriesID,
    //         'codeProduct' => $request->codeProduct,
    //         'position' => $request->position ?? $newPosition,
    //         'description' => $request->description,
    //         'descriptionPromotion' => $request->descriptionPromotion,
    //         'featured' => $request->featured,
    //         'status' => $request->status,
    //         'slug' => $slug,
    //         'deleted' => 0
    //     ]);

    //     // thêm biến thể sản phẩm
    //     foreach ($request->variants as $variantData) {
    //         // tách chuỗi kích thước thành mảng
    //         $sizes = explode(',', $variantData['size']);
    //         $sizes = array_map('trim', $sizes); // loại bỏ khoảng trắng dư
        
    //         // với mỗi kích thước, tạo một biến thể mới
    //         foreach ($sizes as $size) {
    //             $variant = new ProductVariants([
    //                 'ProductID'    => $product->id,
    //                 'color'        => $variantData['color'],
    //                 'size'         => $size,
    //                 'price'        => $variantData['price'],
    //                 'discount'     => $variantData['discount'] ?? 0,
    //                 'specialPrice' => $variantData['specialPrice'] ?? 0,
    //                 'status'       => $variantData['status'],
    //             ]);
    //             $variant->save();
        
    //             // Nếu có hình ảnh thì lưu vào bảng ProductImage
    //             if (!empty($variantData['images'])) {
    //                 foreach ($variantData['images'] as $imageUrl) {
    //                     ProductImage::create([
    //                         'productVariantID' => $variant->id,
    //                         'image'            => $imageUrl,
    //                         'imageName'        => basename($imageUrl),
    //                         'status'           => 1,
    //                         'deleted'          => 0
    //                     ]);
    //                 }
    //             }
    //         }
    //     }
    //     return response()->json([
    //         'code' => 'success',
    //         'message' => 'Sản phẩm được tạo thành công.',
    //         'product' => $product->load('variants.images')
    //     ], 201);
        
    // }

    public function store(Request $request)
    {
        Log::info('Dữ liệu request:', $request->all());
    
        // Kiểm tra dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'brandID' => 'required|integer|exists:brands,id',
            'categoriesID' => 'required|integer|exists:productcategories,id',
            'position' => 'nullable|integer',
            'description' => 'nullable|string',
            'descriptionPromotion' => 'nullable|string',
            'featured' => 'required|boolean',
            'status' => 'required|boolean',
            'codeProduct' => 'nullable|string|max:50',
            
            'variants' => 'required|array|min:1',
            'variants.*.price' => 'required|numeric',
            'variants.*.discount' => 'nullable|numeric',
            'variants.*.specialPrice' => 'nullable|numeric',
            'variants.*.status' => 'required|boolean',
            'variants.*.colors' => 'required|array|min:1',
            'variants.*.sizes' => 'required|array|min:1',
            'variants.*.sizes.*.sizeID' => 'required|integer|exists:sizes,id',
            'variants.*.sizes.*.stock' => 'required|integer|min:0',
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*' => 'required|string|url'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Tự động tăng position
        $maxPosition = Products::max('position') ?? 0;
        $newPosition = $maxPosition + 1;
    
        // Tạo slug
        $slug = generateUniqueSlug($request->title, Products::class);
    
        // Thêm sản phẩm mới
        $product = Products::create([
            'title' => $request->title,
            'brandID' => $request->brandID,
            'categoriesID' => $request->categoriesID,
            'position' => $request->position ?? $newPosition,
            'description' => $request->description,
            'descriptionPromotion' => $request->descriptionPromotion,
            'featured' => $request->featured,
            'status' => $request->status,
            'codeProduct' => $request->codeProduct,
            'slug' => $slug,
            'deleted' => 0
        ]);
    
        // Thêm biến thể sản phẩm
        foreach ($request->variants as $variantData) {
            $variant = ProductVariants::create([
                'ProductID'    => $product->id,
                'price'        => $variantData['price'],
                'stock'        => array_sum(array_column($variantData['sizes'], 'stock')), // Tính tổng stock từ sizes
                'discount'     => $variantData['discount'] ?? 0,
                'specialPrice' => $variantData['specialPrice'] ?? 0,
                'status'       => $variantData['status'],
                'deleted'      => 0
            ]);
    
            // Gán kích thước và màu sắc
            foreach ($variantData['sizes'] as $size) {
                foreach ($variantData['colors'] as $colorID) {
                    VariationOptions::create([
                        'variantId' => $variant->id,
                        'sizeId'    => $size['sizeID'],
                        'colorId'   => $colorID,
                        'stock'     => $size['stock']
                    ]);
                }
            }
    
            // Nếu có hình ảnh thì lưu vào bảng ProductImage
            if (!empty($variantData['images'])) {
                foreach ($variantData['images'] as $imageUrl) {
                    ProductImage::create([
                        'productVariantID' => $variant->id,
                        'image'            => $imageUrl,
                        'imageName'        => basename($imageUrl),
                        'status'           => 1,
                        'deleted'          => 0
                    ]);
                }
            }
        }
        
        return response()->json([
            'code' => 'success',
            'message' => 'Sản phẩm được tạo thành công.',
            'product' => $product->load(['variants.variationOptions', 'variants.images'])
        ], 201);
    }


    // chi tiết sản phẩm
    public function show(string $id)
{
    $product = Products::with([
        'brand:id,name,image', // Thương hiệu
        'category:id,name,image', // Danh mục
        'variants' => function ($query) {
            $query->where('deleted', false)
                ->with([
                    'images:id,productVariantID,image', // Lấy hình ảnh từ bảng productimage
                    'colors:id,name', // Lấy danh sách màu sắc qua bảng trung gian variantoptions
                    'sizes:id,name'  // Lấy danh sách kích thước qua bảng trung gian variantoptions
                ]);
        }
    ])->where('deleted', false)->find($id);

    if (!$product) {
        return response()->json([
            'code' => 'error',
            'message' => 'Sản phẩm không tồn tại.'
        ], 404);
    }

    return response()->json([
        'code' => 'success',
        'message' => 'Chi tiết sản phẩm.',
        'product' => $product
    ], 200);
}
    

    
    

    // update sản phẩm 
    public function update(Request $request, string $id)
{
    // Validate dữ liệu đầu vào
    $validator = Validator::make($request->all(), [
        'title'                => 'required|string|max:255',
        'brandID'              => 'required|integer|exists:brands,id',
        'categoriesID'         => 'required|integer|exists:productcategories,id',
        'position'             => 'nullable|integer',
        'description'          => 'nullable|string',
        'descriptionPromotion' => 'nullable|string',
        'featured'             => 'required|boolean',
        'status'               => 'required|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Tìm sản phẩm cần cập nhật
    $product = Products::find($id);
    if (!$product) {
        return response()->json([
            'code' => 'error',
            'message' => 'Sản phẩm không tồn tại.'
        ], 404);
    }

    $position = $request->has('position') ? $request->position : $product->position;
$slug = $product->title !== $request->input('title')
    ? generateUniqueSlug($request->input('title'), Products::class)
    : $product->slug;

    $product->fill([
        'title'                => $request->title,
        'brandID'              => $request->brandID,
        'categoriesID'         => $request->categoriesID,
        'codeProduct'          => $request->codeProduct,
        'position'             => $position,
        'description'          => $request->description,
        'descriptionPromotion' => $request->descriptionPromotion,
        'featured'             => $request->featured,
        'status'               => $request->status,
        'slug'                 => $slug
    ]);
    $product->save();
    
    return response()->json([
        'code' => 'success',
        'message' => 'Cập nhật sản phẩm thành công.',
        'product' => $product->fresh()
    ], 200);
}


    // xóa sản phẩm vĩnh viễn
    public function destroy($id)
{
    $product = Products::with('variants.images')->find($id);
    if (!$product) {
        return response()->json([
            'code' => 'error',
            'message' => 'Không tìm thấy sản phẩm.'
        ], 404);
    }
    // Xóa tất cả các biến thể 
    foreach ($product->variants as $variant) {
        // Xóa hình ảnh của biến thể
        if ($variant->images()->exists()) {
            $variant->images()->delete();
        }
        $variant->delete();
    }
    $product->delete();

    return response()->json([
        'code' => 'success',
        'message' => 'Sản phẩm đã được xóa vĩnh viễn.'
    ], 200);
}

// xóa mềm
public function softDelete(string $id)
{
    $product = Products::where('deleted', false)->find($id);
    if (!$product) {
        return response()->json([
            'code' => 'error',
            'message' => 'Sản phẩm không tồn tại.'
        ], 404);
    }
    $product->update(['deleted' => true]);

    return response()->json([
        'code' => 'success',
        'message' => 'Xóa sản phẩm thành công.',
    ], 200);
}

// khôi phục
public function restore(string $id)
{
    $product = Products::where('deleted', true)->find($id);
    if (!$product) {
        return response()->json([
            'code' => 'error',
            'message' => 'Sản phẩm không tồn tại.'
        ], 404);
    }
    $product->update(['deleted' => false]);

    return response()->json([
        'code' => 'success',
        'message' => 'Khôi phục sản phẩm thành công.',
    ], 200);
}





    //thêm mới
//     public function store(Request $request)
//     {
//         // Kiểm tra dữ liệu
//         $validator = Validator::make($request->all(), [
//             'title' => 'required|string|max:255',
//             'brandID' => 'required|integer|exists:brands,id',
//             'categoriesID' => 'required|integer|exists:productcategories,id',
//             'codeProduct' => 'required|string|max:50',
//             'position' => 'nullable|integer',
//             'description' => 'nullable|string',
//             'descriptionPromotion' => 'nullable|string',
//             'featured' => 'required|boolean',
//             'status' => 'required|boolean',

//             'variants' => 'required|array|min:1',
//             'variants.*.color' => 'required|string|max:255',
//             'variants.*.size' => 'required|string|max:255',
//             'variants.*.price' => 'required|numeric',
//             'variants.*.discount' => 'nullable|numeric',
//             'variants.*.specialPrice' => 'nullable|numeric',
//             'variants.*.code' => 'required|string|max:255',
//             'variants.*.stock' => 'required|integer',
//             'variants.*.status' => 'required|boolean',
//             'variants.*.images' => 'nullable|array', 
//             'variants.*.images.*' => 'required|string|url'
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors' => $validator->errors()], 422);
//         }

//         // tự động tăng cho possiton
//         $maxPosition = Products::max('position') ?? 0;
//         $newPosition = $maxPosition + 1;

//         // Tạo slug
//         $slug = generateUniqueSlug($request->title, Products::class);

//         // Thêm sản phẩm mới
//         $product = Products::create([
//             'title' => $request->title,
//             'brandID' => $request->brandID,
//             'categoriesID' => $request->categoriesID,
//             'codeProduct' => $request->codeProduct,
//             'position' => $request->position ?? $newPosition,
//             'description' => $request->description,
//             'descriptionPromotion' => $request->descriptionPromotion,
//             'featured' => $request->featured,
//             'status' => $request->status,
//             'slug' => $slug,
//             'deleted' => 0
//         ]);

//         // Thêm biến thể sản phẩm
//         foreach ($request->variants as $variantData) {
//             $variant = new ProductVariants([
//                 'ProductID' => $product->id,
//                 'color' => $variantData['color'],
//                 'size' => $variantData['size'],
//                 'price' => $variantData['price'],
//                 'discount' => $variantData['discount'] ?? 0,
//                 'specialPrice' => $variantData['specialPrice'] ?? 0,
//                 'code' => $variantData['code'],
//                 'stock' => $variantData['stock'],
//                 'status' => $variantData['status'],
//             ]);
//             $variant->save();
//               // Nếu có hình ảnh lưu vào bảng 
//             if (!empty($variantData['images'])) {
//                 foreach ($variantData['images'] as $imageUrl) {
//                     ProductImage::create([
//                         'productVariantID' => $variant->id,
//                         'image' => $imageUrl,
//                         'imageName' => basename($imageUrl), // Lấy tên file từ URL
//                         'status' => 1,
//                         'deleted' => 0
//                     ]);
//                 }
//             }
            
//         }


//         return response()->json([
//             'code' => 'success',
//             'message' => 'Sản phẩm được tạo thành công.',
//             'product' => $product->load('variants.images')
//         ], 201);
        
//     }

// update
// public function update(Request $request, string $id)
// {
//     $validator = Validator::make($request->all(), [
//         'title'                => 'required|string|max:255',
//         'brandID'              => 'required|integer|exists:brands,id',
//         'categoriesID'         => 'required|integer|exists:productcategories,id',
//         'position'             => 'nullable|integer',
//         'description'          => 'nullable|string',
//         'descriptionPromotion' => 'nullable|string',
//         'featured'             => 'required|boolean',
//         'status'               => 'required|boolean',

//         'variants'             => 'required|array|min:1',
//         'variants.*.color'     => 'required|string|max:255',
//         'variants.*.size'      => 'required|string|max:255',
//         'variants.*.price'     => 'required|numeric',
//         'variants.*.discount'  => 'nullable|numeric',
//         'variants.*.specialPrice' => 'nullable|numeric',
//         'variants.*.status'    => 'required|boolean',
//         'variants.*.images'    => 'nullable|array', 
//         'variants.*.images.*'  => 'required|string|url'
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => $validator->errors()], 422);
//     }

//     // Tìm sản phẩm cần cập nhật
//     $product = Products::find($id);
//     if (!$product) {
//         return response()->json([
//             'code' => 'error',
//             'message' => 'Sản phẩm không tồn tại.'
//         ], 404);
//     }

//     // Cập nhật thông tin sản phẩm
//     $product->update([
//         'title'                => $request->title,
//         'brandID'              => $request->brandID,
//         'categoriesID'         => $request->categoriesID,
//         'codeProduct'          => $request->codeProduct,
//         'position'             => $request->position,
//         'description'          => $request->description,
//         'descriptionPromotion' => $request->descriptionPromotion,
//         'featured'             => $request->featured,
//         'status'               => $request->status,
//     ]);

//     // Xóa các biến thể hiện có
//     foreach ($product->variants as $variant) {
//         $variant->images()->delete();
//         $variant->delete();
//     }

//     // Thêm mới các biến thể từ dữ liệu gửi lên
//     foreach ($request->variants as $variantData) {
//         // Nếu dữ liệu size là chuỗi có nhiều kích thước (phân cách bởi dấu phẩy)
//         $sizes = explode(',', $variantData['size']);
//         $sizes = array_map('trim', $sizes);
//         foreach ($sizes as $size) {
//             $variant = new ProductVariants([
//                 'ProductID'    => $product->id,
//                 'color'        => $variantData['color'],
//                 'size'         => $size,
//                 'price'        => $variantData['price'],
//                 'discount'     => $variantData['discount'] ?? 0,
//                 'specialPrice' => $variantData['specialPrice'] ?? 0,
//                 'status'       => $variantData['status'],
//             ]);
//             $variant->save();

//             if (!empty($variantData['images'])) {
//                 foreach ($variantData['images'] as $imageUrl) {
//                     ProductImage::create([
//                         'productVariantID' => $variant->id,
//                         'image'            => $imageUrl,
//                         'imageName'        => basename($imageUrl),
//                         'status'           => 1,
//                         'deleted'          => 0
//                     ]);
//                 }
//             }
//         }
//     }

//     return response()->json([
//         'code' => 'success',
//         'message' => 'Cập nhật sản phẩm thành công.',
//         'product' => $product->load('variants.images')
//     ], 200);
// }

}
