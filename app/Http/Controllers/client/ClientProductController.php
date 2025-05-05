<?php

namespace App\Http\Controllers\client;

use App\Models\Brands;
use App\Models\ProductCategory;
use App\Models\Products;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;

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

        //  Lấy danh sách biến thể theo từng màu sắc
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
    //     try {
    //         $products = Products::select('id', 'title', 'slug', 'position')
    //             ->where('deleted', false)
    //             ->where('status', 1)
    //             ->orderBy('createdAt', 'desc')
    //             ->with([
    //                 'category:id,name',
    //                 'variants' => function ($query) {
    //                     $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
    //                         ->with(['images:id,productVariantID,image'])
    //                         ->orderBy('price', 'ASC');
    //                 }
    //             ])
    //             ->limit(10)
    //             ->get();

    //         if ($products->isEmpty()) {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Không có sản phẩm nào.',
    //                 'data' => []
    //             ], 200);
    //         }

    //         // Định dạng dữ liệu giống API danh mục
    //         $formattedProducts = $products->map(function ($product) {
    //             $firstVariant = $product->variants->first();
    //             $firstImage = $firstVariant ? $firstVariant->images->first() : null;

    //             return [
    //                 'id' => $product->id,
    //                 'title' => $product->title,
    //                 'category' => $product->category ? $product->category->name : null,
    //                 'slug' => $product->slug,
    //                 'image' => $firstImage ? $firstImage->image : null,
    //                 'variant' => $firstVariant ? [
    //                     'id' => $firstVariant->id,
    //                     'price' => $firstVariant->price,
    //                     'specialPrice' => $firstVariant->specialPrice,
    //                     'discount' => $firstVariant->discount
    //                 ] : null
    //             ];
    //         });

    //         return response()->json([
    //             'code' => 'success',
    //             'message' => 'Danh sách 10 sản phẩm mới nhất.',
    //             'data' => $formattedProducts
    //         ], 200);
    //     } catch (\Exception $error) {
    //         return response()->json([
    //             'code' => 'error',
    //             'message' => 'Lỗi khi lấy danh sách sản phẩm.',
    //             'error' => $error->getMessage()
    //         ], 500);
    //     }
    // }
    public function newProducts()
{
    try {
        $products = Products::with([
            'category:id,name',
            'variants' => function ($query) {
                $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
                    ->with(['images:id,productVariantID,image'])
                    ->orderBy('price', 'ASC');
            },
            'reviews' 
        ])
        ->select('id', 'title', 'slug', 'position', 'createdAt')
        ->where('deleted', false)
        ->where('status', 1)
        ->orderBy('createdAt', 'desc')
        ->limit(10)
        ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không có sản phẩm mới nào.',
                'data' => []
            ], 200);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách 10 sản phẩm mới nhất.',
            'data' => ProductResource::collection($products)
        ], 200);
    } catch (\Exception $error) {
        return response()->json([
            'code' => 'error',
            'message' => 'Lỗi khi lấy danh sách sản phẩm mới.',
            'error' => $error->getMessage()
        ], 500);
    }
}


    // sản phẩm nổi bật
    // public function hotProducts()
    // {
    //     try {
    //         $products = Products::select('id', 'title', 'slug', 'position')
    //             ->where('deleted', false)
    //             ->where('status', 1)
    //             ->where('featured', 1) // Chỉ lấy sản phẩm hot
    //             ->orderBy('position', 'desc')
    //             ->with([
    //                 'category:id,name', // Lấy danh mục của sản phẩm
    //                 'variants' => function ($query) {
    //                     $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
    //                         ->with(['images:id,productVariantID,image'])
    //                         ->orderBy('price', 'ASC');
    //                 }
    //             ])
    //             ->limit(10) // Lấy 10 sản phẩm hot
    //             ->get();

    //         if ($products->isEmpty()) {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Không có sản phẩm nổi bật nào.',
    //                 'data' => []
    //             ], 200);
    //         }

    //         // Định dạng dữ liệu giống với API danh mục
    //         $formattedProducts = $products->map(function ($product) {
    //             $firstVariant = $product->variants->first();
    //             $firstImage = $firstVariant ? $firstVariant->images->first() : null;

    //             return [
    //                 'id' => $product->id,
    //                 'title' => $product->title,
    //                 'category' => $product->category ? $product->category->name : null,
    //                 'slug' => $product->slug,
    //                 'image' => $firstImage ? $firstImage->image : null,
    //                 'variant' => $firstVariant ? [
    //                     'id' => $firstVariant->id,
    //                     'price' => $firstVariant->price,
    //                     'specialPrice' => $firstVariant->specialPrice,
    //                     'discount' => $firstVariant->discount
    //                 ] : null
    //             ];
    //         });

    //         return response()->json([
    //             'code' => 'success',
    //             'message' => 'Danh sách 10 sản phẩm nổi bật.',
    //             'data' => $formattedProducts
    //         ], 200);
    //     } catch (\Exception $error) {
    //         return response()->json([
    //             'code' => 'error',
    //             'message' => 'Lỗi khi lấy danh sách sản phẩm hot.',
    //             'error' => $error->getMessage()
    //         ], 500);
    //     }
    // }
    public function hotProducts()
{
    try {
        $products = Products::with([
            'category:id,name',
            'reviews',
            'variants' => function ($query) {
                $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
                    ->with(['images:id,productVariantID,image'])
                    ->orderBy('price', 'ASC');
            }
        ])
        ->select('id', 'title', 'slug', 'position')
        ->where('deleted', false)
        ->where('status', 1)
        ->where('featured', 1)
        ->orderBy('position', 'desc')
        ->limit(10)
        ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không có sản phẩm nổi bật nào.',
                'data' => []
            ], 200);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách 10 sản phẩm nổi bật.',
            'data' => ProductResource::collection($products)
        ], 200);
    } catch (\Exception $error) {
        return response()->json([
            'code' => 'error',
            'message' => 'Lỗi khi lấy danh sách sản phẩm hot.',
            'error' => $error->getMessage()
        ], 500);
    }
}

    // sản phẩm sale
    public function saleProducts()
    {
        try {
            $products = Products::select('id', 'title', 'slug', 'position')
                ->where('deleted', false)
                ->where('status', 1)
                ->with([
                    'category:id,name',
                    'variants' => function ($query) {
                        $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
                            ->with(['images:id,productVariantID,image'])
                            ->orderBy('discount', 'desc')
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
            $formattedProducts = $formattedProducts->sortByDesc(function ($item) {
                return $item['variant']['discount'] ?? 0;
            })->values();
            
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

    // tìm kiếm sản phẩm
    // public function search(Request $request)
    // {
    //     try {
    //         $query = $request->query('query');
    //         $perPage = $request->query('per_page', 15);

    //         if (!$query || trim($query) === '') {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Vui lòng nhập từ khóa tìm kiếm.'
    //             ], 400);
    //         }

    //         // Tìm kiếm sản phẩm theo title hoặc description
    //         $products = Products::select('id', 'title', 'slug', 'position')
    //             ->where('deleted', false)
    //             ->where('status', 1)
    //             ->where(function ($q) use ($query) {
    //                 $q->where('title', 'LIKE', "%$query%")
    //                     ->orWhere('description', 'LIKE', "%$query%");
    //             })
    //             ->with([
    //                 'category:id,name', // Lấy danh mục sản phẩm
    //                 'variants' => function ($query) {
    //                     $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
    //                         ->with([
    //                             'images:id,productVariantID,image'
    //                         ])
    //                         ->orderBy('discount', 'DESC');
    //                 }
    //             ])
    //             ->orderBy('position', 'DESC')
    //             ->paginate($perPage);

    //         if ($products->isEmpty()) {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Không tìm thấy sản phẩm phù hợp.',
    //                 'data' => []
    //             ], 404);
    //         }

    //         // Format lại dữ liệu
    //         $formattedProducts = $products->map(function ($product) {
    //             $firstVariant = $product->variants->first();
    //             $firstImage = $firstVariant ? $firstVariant->images->first() : null;

    //             return [
    //                 'id' => $product->id,
    //                 'title' => $product->title,
    //                 'category' => $product->category ? $product->category->name : null,
    //                 'slug' => $product->slug,
    //                 'position' => $product->position,
    //                 'image' => $firstImage ? $firstImage->image : null,
    //                 'variant' => $firstVariant ? [
    //                     'id' => $firstVariant->id,
    //                     'price' => $firstVariant->price,
    //                     'specialPrice' => $firstVariant->specialPrice,
    //                     'discount' => $firstVariant->discount
    //                 ] : null
    //             ];
    //         });

    //         return response()->json([
    //             'code' => 'success',
    //             'message' => 'Tìm kiếm sản phẩm thành công.',
    //             'data' => $formattedProducts,
    //             'meta' => [
    //                 'current_page' => $products->currentPage(),
    //                 'last_page' => $products->lastPage(),
    //                 'per_page' => $products->perPage(),
    //                 'total' => $products->total(),
    //             ]
    //         ], 200);
    //     } catch (\Exception $error) {
    //         return response()->json([
    //             'code' => 'error',
    //             'message' => 'Đã có lỗi xảy ra!',
    //             'error' => $error->getMessage()
    //         ], 500);
    //     }
    // }
    public function search(Request $request)
{
    try {
        $query = $request->query('query');
        $perPage = $request->query('per_page', 15);

        if (!$query || trim($query) === '') {
            return response()->json([
                'code' => 'error',
                'message' => 'Vui lòng nhập từ khóa tìm kiếm.'
            ], 400);
        }

        // Tìm kiếm sản phẩm theo title hoặc description
        $products = Products::where('deleted', false)
            ->where('status', 1)
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%$query%")
                  ->orWhere('description', 'LIKE', "%$query%");
            })
            ->with([
                'category:id,name',
                'variants' => function ($query) {
                    $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
                        ->with(['images:id,productVariantID,image'])
                        ->orderBy('discount', 'DESC');
                },
                'reviews' // Để lấy rating và review count
            ])
            ->orderBy('position', 'DESC')
            ->paginate($perPage);

        if ($products->isEmpty()) {
            return response()->json([
                'code' => 'error',
                'message' => 'Không tìm thấy sản phẩm phù hợp.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Tìm kiếm sản phẩm thành công.',
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
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
    // public function getProductsByCategoryId($id)
    // {
    //     try {
    //         // Kiểm tra danh mục có tồn tại không
    //         $category = ProductCategory::where('id', $id)
    //             ->where('deleted', false)
    //             ->where('status', 1)
    //             ->select('id', 'name')
    //             ->first();

    //         if (!$category) {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Danh mục không hợp lệ!'
    //             ], 400);
    //         }

    //         // Lấy danh sách ID danh mục cha và danh mục con
    //         $categoryIds = [$category->id];

    //         $subCategoryIds = ProductCategory::where('parentID', $category->id)
    //             ->where('deleted', false)
    //             ->where('status', 1)
    //             ->pluck('id')
    //             ->toArray();

    //         if (!empty($subCategoryIds)) {
    //             $categoryIds = array_merge($categoryIds, $subCategoryIds);
    //         }

    //         // Tìm sản phẩm theo danh mục cha và con
    //         $products = Products::select('id', 'title', 'slug', 'position')
    //             ->whereIn('categoriesID', $categoryIds)
    //             ->where('deleted', false)
    //             ->where('status', 1)
    //             ->with([
    //                 'category:id,name',
    //                 'variants' => function ($query) {
    //                     $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
    //                         ->with(['images:id,productVariantID,image'])
    //                         ->orderBy('price', 'ASC');
    //                 }
    //             ])
    //             ->orderBy('position', 'DESC')
    //             ->limit(6)
    //             ->get();

    //         if ($products->isEmpty()) {
    //             return response()->json([
    //                 'code' => 'success',
    //                 'message' => 'Không có sản phẩm nào trong danh mục này.',
    //                 'data' => []
    //             ], 200);
    //         }

    //         // Định dạng dữ liệu trả về
    //         $formattedProducts = $products->map(function ($product) {
    //             $firstVariant = $product->variants->first();
    //             $firstImage = $firstVariant ? $firstVariant->images->first() : null;

    //             return [
    //                 'id' => $product->id,
    //                 'title' => $product->title,
    //                 'category' => $product->category ? $product->category->name : null,
    //                 'slug' => $product->slug,
    //                 'image' => $firstImage ? $firstImage->image : null,
    //                 'variant' => $firstVariant ? [
    //                     'id' => $firstVariant->id,
    //                     'price' => $firstVariant->price,
    //                     'specialPrice' => $firstVariant->specialPrice,
    //                     'discount' => $firstVariant->discount
    //                 ] : null
    //             ];
    //         });

    //         return response()->json([
    //             'code' => 'success',
    //             'message' => 'Lấy danh sách sản phẩm theo danh mục thành công.',
    //             'data' => $formattedProducts
    //         ], 200);
    //     } catch (\Exception $error) {
    //         return response()->json([
    //             'code' => 'error',
    //             'message' => 'Lỗi khi lấy danh sách sản phẩm.',
    //             'error' => $error->getMessage()
    //         ], 500);
    //     }
    // }
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

        // Lấy danh sách sản phẩm theo danh mục
        $products = Products::whereIn('categoriesID', $categoryIds)
            ->where('deleted', false)
            ->where('status', 1)
            ->with([
                'category:id,name',
                'variants' => function ($query) {
                    $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
                        ->with(['images:id,productVariantID,image'])
                        ->orderBy('price', 'ASC');
                },
                'reviews' // Thêm để lấy đánh giá
            ])
            ->orderBy('position', 'DESC')
            ->limit(6)
            ->get();

        return response()->json([
            'code' => 'success',
            'message' => 'Lấy danh sách sản phẩm theo danh mục thành công.',
            'data' => ProductResource::collection($products)
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
    // public function getProductsByCategorySlug($slug, Request $request)
    // {
    //     try {
    //         // Tìm danh mục theo slug
    //         $category = ProductCategory::where('slug', $slug)
    //             ->where('deleted', false)
    //             ->where('status', 1)
    //             ->select('id', 'name', 'slug')
    //             ->first();

    //         if (!$category) {
    //             return response()->json([
    //                 'code' => 'error',
    //                 'message' => 'Không tìm thấy danh mục!'
    //             ], 404);
    //         }

    //         // Lấy danh mục con nếu có
    //         $categoryIds = [$category->id];

    //         $subCategories = ProductCategory::where('parentID', $category->id)
    //             ->where('deleted', false)
    //             ->where('status', 1)
    //             ->pluck('id')
    //             ->toArray();

    //         if (!empty($subCategories)) {
    //             $categoryIds = array_merge($categoryIds, $subCategories);
    //         }

    //         // Lấy sản phẩm theo danh mục cha và con
    //         $products = Products::select('id', 'title', 'slug', 'position')
    //             ->whereIn('categoriesID', $categoryIds)
    //             ->where('deleted', false)
    //             ->where('status', 1)
    //             ->withMin('variants', 'specialPrice')
    //             ->with([
    //                 'category:id,name,slug',
    //                 'variants' => function ($query) {
    //                     $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
    //                         ->with(['images:id,productVariantID,image'])
    //                         ->orderBy('price', 'ASC');
    //                 }
    //             ]);
                
    //         $priceMin = $request->get('price_min');
    //         $priceMax = $request->get('price_max');
            
    //         if ($priceMin || $priceMax) {
    //             $products->whereHas('variants', function ($query) use ($priceMin, $priceMax) {
    //                 if ($priceMin !== null) {
    //                     $query->where('specialPrice', '>=', $priceMin);
    //                 }
    //                 if ($priceMax !== null) {
    //                     $query->where('specialPrice', '<=', $priceMax);
    //                 }
    //             });
    //         }   

    //         // Xử lý sort
    //         $sort = $request->get('sort');
    //         switch ($sort) {
    //             case 'price_asc':
    //                 $products->orderBy('variants_min_special_price', 'ASC');
    //                 break;
    //             case 'price_desc':
    //                 $products->orderBy('variants_min_special_price', 'DESC');
    //                 break;
    //             case 'newest':
    //                 $products->orderBy('createdAt', 'DESC');
    //                 break;
    //             case 'oldest':
    //                 $products->orderBy('createdAt', 'ASC');
    //                 break;
    //             default:
    //                 $products->orderBy('position', 'ASC');
    //                 break;
    //         }

    //         $products = $products->paginate(15);

    //         if ($products->isEmpty()) {
    //             return response()->json([
    //                 'code' => 'success',
    //                 'message' => 'Không có sản phẩm nào trong danh mục này.',
    //                 'data' => []
    //             ], 200);
    //         }

    //         // Định dạng dữ liệu trả về
    //         $formattedProducts = $products->map(function ($product) {
    //             $firstVariant = $product->variants->first();
    //             $firstImage = $firstVariant ? $firstVariant->images->first() : null;

    //             return [
    //                 'id' => $product->id,
    //                 'title' => $product->title,
    //                 'slug' => $product->slug,
    //                 'position' => $product->position,
    //                 'category' => $product->category ? $product->category->name : null,
    //                 'image' => $firstImage ? $firstImage->image : null,
    //                 'variant' => $firstVariant ? [
    //                     'id' => $firstVariant->id,
    //                     'price' => $firstVariant->price,
    //                     'specialPrice' => $firstVariant->specialPrice,
    //                     'discount' => $firstVariant->discount
    //                 ] : null
    //             ];
    //         });

    //         return response()->json([
    //             'code' => 'success',
    //             'message' => "{$category->name}",
    //             'data' => $formattedProducts,
    //             'meta' => [
    //                 'current_page' => $products->currentPage(),
    //                 'last_page' => $products->lastPage(),
    //                 'per_page' => $products->perPage(),
    //                 'total' => $products->total(),
    //             ]
    //         ], 200);
    //     } catch (\Exception $error) {
    //         return response()->json([
    //             'code' => 'error',
    //             'message' => 'Lỗi khi lấy danh sách sản phẩm.',
    //             'error' => $error->getMessage()
    //         ], 500);
    //     }
    // }
    public function getProductsByCategorySlug($slug, Request $request)
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

        // Lấy ID danh mục cha và con
        $categoryIds = [$category->id];
        $subCategoryIds = ProductCategory::where('parentID', $category->id)
            ->where('deleted', false)
            ->where('status', 1)
            ->pluck('id')
            ->toArray();
        $categoryIds = array_merge($categoryIds, $subCategoryIds);

        // Query sản phẩm
        $productsQuery = Products::whereIn('categoriesID', $categoryIds)
            ->where('deleted', false)
            ->where('status', 1)
            ->withMin('variants', 'specialPrice')
            ->with([
                'category:id,name,slug',
                'variants' => function ($query) {
                    $query->select('id', 'ProductID', 'price', 'specialPrice', 'discount')
                        ->with(['images:id,productVariantID,image'])
                        ->orderBy('price', 'ASC');
                },
                'reviews'
            ]);

        // Lọc theo giá
        $priceMin = $request->get('price_min');
        $priceMax = $request->get('price_max');
        if ($priceMin || $priceMax) {
            $productsQuery->whereHas('variants', function ($query) use ($priceMin, $priceMax) {
                if ($priceMin !== null) {
                    $query->where('specialPrice', '>=', $priceMin);
                }
                if ($priceMax !== null) {
                    $query->where('specialPrice', '<=', $priceMax);
                }
            });
        }

        // Sắp xếp
        switch ($request->get('sort')) {
            case 'price_asc':
                $productsQuery->orderBy('variants_min_special_price', 'ASC');
                break;
            case 'price_desc':
                $productsQuery->orderBy('variants_min_special_price', 'DESC');
                break;
            case 'newest':
                $productsQuery->orderBy('createdAt', 'DESC');
                break;
            case 'oldest':
                $productsQuery->orderBy('createdAt', 'ASC');
                break;
            default:
                $productsQuery->orderBy('position', 'ASC');
                break;
        }

        // Phân trang
        $products = $productsQuery->paginate(15);

        return response()->json([
            'code' => 'success',
            'message' => $category->name,
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
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

    public function getProductWithRating($slug)
{
    $product = Products::where('slug', $slug)
        ->with(['variants', 'reviews'])
        ->firstOrFail();

    $averageRating = $product->reviews()->avg('star');
    $totalReviews = $product->reviews()->count();

    return response()->json([
        'product' => $product,
        'average_rating' => round($averageRating, 1),
        'total_reviews' => $totalReviews
    ]);
}
}


