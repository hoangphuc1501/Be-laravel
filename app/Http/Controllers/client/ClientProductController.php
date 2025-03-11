<?php

namespace App\Http\Controllers\client;

use App\Models\Brands;
use App\Models\ProductCategory;
use App\Models\Products;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class ClientProductController extends Controller
{
    // chi tiết sản phẩm
    // public function showBySlug(string $slug)
    // {
    //     $product = Products::with([
    //         'brand:id,name', // Thương hiệu
    //         'category:id,name', // Danh mục
    //         'variants' => function ($query) {
    //             $query->where('deleted', false)
    //                 ->with([
    //                     'images:id,productVariantID,image', // Lấy hình ảnh từ bảng productimage
    //                     'colors:id,name', // Lấy danh sách màu sắc qua bảng trung gian variantoptions
    //                     'sizes:id,name'  // Lấy danh sách kích thước qua bảng trung gian variantoptions
    //                 ]);
    //         }
    //     ])->where('slug', $slug)
    //         ->where('deleted', false)
    //         ->first();

    //     if (!$product) {
    //         return response()->json([
    //             'code' => 'error',
    //             'message' => 'Sản phẩm không tồn tại.'
    //         ], 404);
    //     }

    //     return response()->json([
    //         'code' => 'success',
    //         'message' => 'Chi tiết sản phẩm.',
    //         'product' => $product
    //     ], 200);
    // }
    public function showBySlug(string $slug)
{
    $product = Products::with([
        'brand:id,name', // Thương hiệu
        'category:id,name', // Danh mục
        'variants' => function ($query) {
            $query->where('deleted', false)
                ->with([
                    'images:id,productVariantID,image', // Hình ảnh
                    'colors:id,name', // Màu sắc
                    'sizes:id,name'  // Kích thước
                ]);
        }
    ])->where('slug', $slug)
        ->where('deleted', false)
        ->first();

    if (!$product) {
        return response()->json([
            'code' => 'error',
            'message' => 'Sản phẩm không tồn tại.'
        ], 404);
    }

    // ✅ Lấy danh sách biến thể theo từng màu sắc
    $formattedVariants = [];
    foreach ($product->variants as $variant) {
        $color = optional($variant->colors->first());
        $colorId = $color->id ?? null;
        $colorName = $color->name ?? "Không xác định";
        if (!isset($formattedVariants[$colorName])) {
            $formattedVariants[$colorName] = [
                'id' => $variant->id,
                'colorId' => $colorId,
                'colorName' => $colorName,
                'price' => $variant->price,
                'specialPrice' => $variant->specialPrice,
                'discount' => $variant->discount,
                'stock' => $variant->stock,
                'images' => $variant->images->map(fn($img) => $img->image)->toArray(),
                'sizes' => $variant->sizes->map(fn($size) => [
                    'id' => $size->id,   
                    'name' => $size->name 
                ])
            ];
        }
        // Thêm size vào danh sách
        // $formattedVariants[$colorName]['sizes'] = array_merge(
        //     $formattedVariants[$colorName]['sizes'],
        //     $variant->sizes->pluck('name')->toArray()
        // );
    }

    return response()->json([
        'code' => 'success',
        'message' => 'Chi tiết sản phẩm.',
        'product' => [
            'id' => $product->id,
            'title' => $product->title,
            'codeProduct' => $product->codeProduct,
            'brand' => [
                'id' => $product->brand->id,
                'name' => $product->brand->name,
            ],
            'category' => [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ],
            'description' => $product->description,
            'descriptionPromotion' => $product->descriptionPromotion,
            'featured' => $product->featured,
            'status' => $product->status,
            'price' => optional($product->variants->first())->price,
            'specialPrice' => optional($product->variants->first())->specialPrice,
            'discount' => optional($product->variants->first())->discount,
            'variants' => array_values($formattedVariants),
            'stock' => $product->variants->sum('stock')
        ]
    ], 200);
}


    // danh sách sản phẩm
    public function productList()
{
    $products = Products::select('id', 'title', 'codeProduct', 'brandID', 'categoriesID', 'slug', 'position', 'description', 'featured', 'descriptionPromotion', 'status')
    ->where('deleted', false)
    ->where('status', 1)
    ->orderBy('position', 'desc')
    ->with([
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

// sản phẩm mới 
// public function newProducts()
// {
//     $products = Products::select('id', 'title', 'slug', 'position', 'featured', 'status', 'createdAt')
//         ->where('deleted', false)
//         ->where('status', 1)
//         ->orderBy('createdAt', 'desc') 
//         ->with([
//             'variants' => function ($query) {
//                 $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
//                     ->with(['images:id,productVariantID,image']);
//             }
//         ])
//         ->limit(10) 
//         ->get();

//     if ($products->isEmpty()) {
//         return response()->json([
//             'code' => 'error',
//             'message' => 'Không có sản phẩm nào.',
//             'data' => []
//         ], 200);
//     }

//     return response()->json([
//         'code' => 'success',
//         'message' => 'Danh sách 10 sản phẩm mới nhất.',
//         'data' => $products
//     ], 200);
// }
public function newProducts()
{
    try {
        $products = Products::select('id', 'title', 'slug', 'position')
            ->where('deleted', false)
            ->where('status', 1)
            ->orderBy('createdAt', 'desc')
            ->with([
                'category:id,name',
                'variants' => function ($query) {
                    $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
                        ->with(['images:id,productVariantID,image'])
                        ->orderBy('price', 'ASC');
                }
            ])
            ->limit(10)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không có sản phẩm nào.',
                'data' => []
            ], 200);
        }

        // Định dạng dữ liệu giống API danh mục
        $formattedProducts = $products->map(function ($product) {
            $firstVariant = $product->variants->first();
            $firstImage = $firstVariant ? $firstVariant->images->first() : null;

            return [
                'id' => $product->id,
                'title' => $product->title,
                'category' => $product->category ? $product->category->name : null,
                'slug' => $product->slug,
                'image' => $firstImage ? $firstImage->image : null,
                'variant' => $firstVariant ? [
                    'id' => $firstVariant->id,
                    'price' => $firstVariant->price,
                    'specialPrice' => $firstVariant->specialPrice,
                    'discount' => $firstVariant->discount
                ] : null
            ];
        });

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách 10 sản phẩm mới nhất.',
            'data' => $formattedProducts
        ], 200);
    } catch (\Exception $error) {
        return response()->json([
            'code' => 'error',
            'message' => 'Lỗi khi lấy danh sách sản phẩm.',
            'error' => $error->getMessage()
        ], 500);
    }
}


// sản phẩm nổi bật
public function hotProducts()
{
    try {
        $products = Products::select('id', 'title', 'slug', 'position')
            ->where('deleted', false)
            ->where('status', 1)
            ->where('featured', 1) // Chỉ lấy sản phẩm hot
            ->orderBy('position', 'desc') 
            ->with([
                'category:id,name', // Lấy danh mục của sản phẩm
                'variants' => function ($query) {
                    $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
                        ->with(['images:id,productVariantID,image'])
                        ->orderBy('price', 'ASC');
                }
            ])
            ->limit(10) // Lấy 10 sản phẩm hot
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không có sản phẩm nổi bật nào.',
                'data' => []
            ], 200);
        }

        // Định dạng dữ liệu giống với API danh mục
        $formattedProducts = $products->map(function ($product) {
            $firstVariant = $product->variants->first();
            $firstImage = $firstVariant ? $firstVariant->images->first() : null;

            return [
                'id' => $product->id,
                'title' => $product->title,
                'category' => $product->category ? $product->category->name : null,
                'slug' => $product->slug,
                'image' => $firstImage ? $firstImage->image : null,
                'variant' => $firstVariant ? [
                    'id' => $firstVariant->id,
                    'price' => $firstVariant->price,
                    'specialPrice' => $firstVariant->specialPrice,
                    'discount' => $firstVariant->discount
                ] : null
            ];
        });

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách 10 sản phẩm nổi bật.',
            'data' => $formattedProducts
        ], 200);
    } catch (\Exception $error) {
        return response()->json([
            'code' => 'error',
            'message' => 'Lỗi khi lấy danh sách sản phẩm hot.',
            'error' => $error->getMessage()
        ], 500);
    }
}


// tìm kiếm sản phẩm
public function search(Request $request)
{
    try {
        $query = $request->query('query');

        if (!$query || trim($query) === '') {
            return response()->json([
                'code' => 'error',
                'message' => 'Vui lòng nhập từ khóa tìm kiếm.'
            ], 400);
        }

        // Tìm kiếm sản phẩm theo title hoặc description
        $products = Products::select('id', 'title', 'slug', 'position')
            ->where('deleted', false)
            ->where('status', 1)
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%$query%")
                    ->orWhere('description', 'LIKE', "%$query%");
            })
            ->with([
                'category:id,name', // Lấy danh mục sản phẩm
                'variants' => function ($query) {
                    $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
                        ->with([
                            'images:id,productVariantID,image' // Lấy ảnh của sản phẩm
                        ])
                        ->orderBy('discount', 'DESC'); // Sắp xếp theo giảm giá cao nhất
                }
            ])
            ->orderBy('position', 'DESC') // Sắp xếp theo vị trí ưu tiên
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không tìm thấy sản phẩm phù hợp.',
                'data' => []
            ], 404);
        }

        // Format lại dữ liệu
        $formattedProducts = $products->map(function ($product) {
            $firstVariant = $product->variants->first();
            $firstImage = $firstVariant ? $firstVariant->images->first() : null;

            return [
                'id' => $product->id,
                'title' => $product->title,
                'category' => $product->category ? $product->category->name : null,
                'slug' => $product->slug,
                'position' => $product->position,
                'image' => $firstImage ? $firstImage->image : null,
                'variant' => $firstVariant ? [
                    'id' => $firstVariant->id,
                    'price' => $firstVariant->price,
                    'specialPrice' => $firstVariant->specialPrice,
                    'discount' => $firstVariant->discount
                ] : null
            ];
        });

        return response()->json([
            'code' => 'success',
            'message' => 'Tìm kiếm sản phẩm thành công.',
            'data' => $formattedProducts
        ], 200);
    } catch (\Exception $error) {
        return response()->json([
            'code' => 'error',
            'message' => 'Đã có lỗi xảy ra!',
            'error' => $error->getMessage()
        ], 500);
    }
}

// sản phẩm theo id danh muc
public function getProductsByCategoryId($id)
{
    try {
        // Kiểm tra danh mục có tồn tại không
        $category = ProductCategory::where('id', $id)
            ->where('deleted', false)
            ->where('status', 1)
            ->select('id', 'name')
            ->first();

        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục không hợp lệ!'
            ], 400);
        }

        // Lấy danh sách ID danh mục cha và danh mục con
        $categoryIds = [$category->id];

        $subCategoryIds = ProductCategory::where('parentID', $category->id)
            ->where('deleted', false)
            ->where('status', 1)
            ->pluck('id')
            ->toArray();

        if (!empty($subCategoryIds)) {
            $categoryIds = array_merge($categoryIds, $subCategoryIds);
        }

        // Tìm sản phẩm theo danh mục cha và con
        $products = Products::select('id', 'title', 'slug', 'position')
            ->whereIn('categoriesID', $categoryIds)
            ->where('deleted', false)
            ->where('status', 1)
            ->with([
                'category:id,name',
                'variants' => function ($query) {
                    $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
                        ->with(['images:id,productVariantID,image'])
                        ->orderBy('price', 'ASC');
                }
            ])
            ->orderBy('position', 'DESC')
            ->limit(6)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'code' => 'success',
                'message' => 'Không có sản phẩm nào trong danh mục này.',
                'data' => []
            ], 200);
        }

        // Định dạng dữ liệu trả về
        $formattedProducts = $products->map(function ($product) {
            $firstVariant = $product->variants->first();
            $firstImage = $firstVariant ? $firstVariant->images->first() : null;

            return [
                'id' => $product->id,
                'title' => $product->title,
                'category' => $product->category ? $product->category->name : null,
                'slug' => $product->slug,
                'image' => $firstImage ? $firstImage->image : null,
                'variant' => $firstVariant ? [
                    'id' => $firstVariant->id,
                    'price' => $firstVariant->price,
                    'specialPrice' => $firstVariant->specialPrice,
                    'discount' => $firstVariant->discount
                ] : null
            ];
        });

        return response()->json([
            'code' => 'success',
            'message' => 'Lấy danh sách sản phẩm theo danh mục thành công.',
            'data' => $formattedProducts
        ], 200);
    } catch (\Exception $error) {
        return response()->json([
            'code' => 'error',
            'message' => 'Lỗi khi lấy danh sách sản phẩm.',
            'error' => $error->getMessage()
        ], 500);
    }
}

// sản phẩm theo slug danh mục 
public function getProductsByCategorySlug($slug)
{
    try {
        // Tìm danh mục theo slug
        $category = ProductCategory::where('slug', $slug)
            ->where('deleted', false)
            ->where('status', 1)
            ->select('id', 'name', 'slug')
            ->first();

        if (!$category) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không tìm thấy danh mục!'
            ], 404);
        }

        // Lấy danh mục con nếu có
        $categoryIds = [$category->id];

        $subCategories = ProductCategory::where('parentID', $category->id)
            ->where('deleted', false)
            ->where('status', 1)
            ->pluck('id')
            ->toArray();

        if (!empty($subCategories)) {
            $categoryIds = array_merge($categoryIds, $subCategories);
        }

        // Lấy sản phẩm theo danh mục cha và con
        $products = Products::select('id', 'title', 'slug', 'position')
            ->whereIn('categoriesID', $categoryIds)
            ->where('deleted', false)
            ->where('status', 1)
            ->with([
                'category:id,name,slug',
                'variants' => function ($query) {
                    $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
                        ->with(['images:id,productVariantID,image'])
                        ->orderBy('price', 'ASC');
                }
            ])
            ->limit(12)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'code' => 'success',
                'message' => 'Không có sản phẩm nào trong danh mục này.',
                'data' => []
            ], 200);
        }

        // Định dạng dữ liệu trả về
        $formattedProducts = $products->map(function ($product) {
            $firstVariant = $product->variants->first();
            $firstImage = $firstVariant ? $firstVariant->images->first() : null;

            return [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'position' => $product->position,
                'category' => $product->category ? $product->category->name : null,
                'image' => $firstImage ? $firstImage->image : null,
                'variant' => $firstVariant ? [
                    'id' => $firstVariant->id,
                    'price' => $firstVariant->price,
                    'specialPrice' => $firstVariant->specialPrice,
                    'discount' => $firstVariant->discount
                ] : null
            ];
        });

        return response()->json([
            'code' => 'success',
            'message' => "{$category->name}",
            'data' => $formattedProducts
        ], 200);
    } catch (\Exception $error) {
        return response()->json([
            'code' => 'error',
            'message' => 'Lỗi khi lấy danh sách sản phẩm.',
            'error' => $error->getMessage()
        ], 500);
    }
}

// danh mục sản phẩm cha
    public function categoryParent()
    {
        try {
            $categoryParent = ProductCategory::select('id', 'name', 'image', 'description', 'slug', 'parentID', 'position')
                ->where('deleted', false)
                ->where('status', 1)
                ->whereNull('parentID') 
                ->orderBy('position', 'asc')
                ->get();

            return response()->json([
                'code' => 'success',
                'message' => 'Hiển thị danh mục sản phẩm thành công.',
                'categoryParent' => $categoryParent
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi khi lấy danh mục sản phẩm.',
                'error' => $error->getMessage()
            ], 500);
        }
    }

// danh mục sản phẩm cha và con
public function getCategories()
{
    try {
        $categories = ProductCategory::select('id', 'name', 'image', 'description', 'slug', 'parentID', 'position')
            ->where('deleted', false)
            ->where('status', 1)
            ->whereNull('parentID') 
            ->orderBy('position', 'asc') 
            ->with('children:id,name,image,description,slug,parentID,position')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Danh mục không tồn tại!',
                'categories' => []
            ], 404);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Hiển thị danh mục sản phẩm thành công.',
            'categories' => $categories
        ], 200);
    } catch (\Exception $error) {
        return response()->json([
            'code' => 'error',
            'message' => 'Lỗi khi lấy danh mục sản phẩm.',
            'error' => $error->getMessage()
        ], 500);
    }
}

// danh sách thương hiệu
public function getBrands()
    {
        try {
            $brands = Brands::select('id', 'name', 'image', 'description', 'slug', 'position')
                ->where('deleted', false)
                ->where('status', 1)
                ->orderBy('position', 'asc')
                ->get();

            return response()->json([
                'code' => 'success',
                'message' => 'Danh sách thương hiệu.',
                'brands' => $brands
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'code' => 'error',
                'message' => 'Lỗi khi lấy danh sách thương hiệu.',
                'error' => $error->getMessage()
            ], 500);
        }
    }


}


