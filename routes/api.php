<?php

// use App\Http\Controllers\BrandController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\client\CartController;
use App\Http\Controllers\client\ClientUerController;
use App\Http\Controllers\client\ClientOrderController;
use App\Http\Controllers\NewsCategoryController;
use App\Http\Controllers\Admin\ProductVariantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\ProductSpecificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentAndRatingController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\client\ClientProductController;
use App\Http\Controllers\VNPayController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// admin

Route::prefix('admin')->group(function () {
// api sản phẩm
Route::apiResource('products', ProductController::class);
Route::prefix('products')->group(function () {
    Route::patch('softDelete/{id}', [ProductController::class, 'softDelete']); 
    Route::patch('restore/{id}', [ProductController::class, 'restore']); 
});

// api biến thể sản phẩm
Route::apiResource('productVariants', ProductVariantController::class);
Route::prefix('productVariants')->group(function () {
    Route::patch('softDelete/{id}', [ProductVariantController::class, 'softDelete']); 
    Route::patch('restore/{id}', [ProductVariantController::class, 'restore']); 
});

    
// api color
    Route::apiResource('colors', ColorController::class);
    Route::prefix('colors')->group(function () {
        Route::patch('/softDelete/{id}', [ColorController::class, 'softDelete']);
        Route::patch('/restore/{id}', [ColorController::class, 'restore']);
    });
    Route::get('listColor', [ColorController::class, 'ListColor']);

// api size
    Route::apiResource('sizes', SizeController::class);
    Route::prefix('sizes')->group(function () {
        Route::patch('/softDelete/{id}', [SizeController::class, 'softDelete']);
        Route::patch('/restore/{id}', [SizeController::class, 'restore']);
    });
    Route::get('listSize', [SizeController::class, 'listSize']);

// api thương hiệu
Route::apiResource('brands', BrandController::class);
Route::prefix('brands')->group(function () {
    Route::patch('/softDelete/{id}', [BrandController::class, 'softDelete']);
    Route::patch('/restore/{id}', [BrandController::class, 'restore']);
});
Route::get('ListBrands', [BrandController::class, 'listBrands']);

// api danh mục sản phẩm
Route::apiResource('productcategories', ProductCategoryController::class);
Route::prefix('productcategories')->group(function () {
    // Route::get('ListCategory', [ProductCategoryController::class, 'ListCategory']);
    Route::patch('softDelete/{id}', [ProductCategoryController::class, 'softDelete']); 
    Route::patch('restore/{id}', [ProductCategoryController::class, 'restore']); 
});
Route::get('ListCategory', [ProductCategoryController::class, 'ListCategory']);

// đơn hàng
Route::get('/orders', [OrderController::class, 'index']);
Route::patch('/orders/{id}', [OrderController::class, 'update']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
});

// api thông số kỹ  thuật
Route::apiResource('productspecifications', ProductSpecificationController::class);
Route::prefix('productspecifications')->group(function () {
    Route::patch('/softDelete/{id}', [ProductSpecificationController::class, 'softDelete']);
    Route::patch('/restore/{id}', [ProductSpecificationController::class, 'restore']);
});

// api users
Route::apiResource('users', UserController::class);
Route::prefix('users')->group(function () {
    Route::patch('/softDelete/{id}', [UserController::class, 'softDelete']);
    Route::patch('/restore/{id}', [UserController::class, 'restore']);
});


// api danh mục tin tức
Route::apiResource('newsCategories', NewsCategoryController::class);
Route::prefix('newsCategories')->group(function () {
    Route::patch('softDelete/{id}', [NewsCategoryController::class, 'softDelete']); 
    Route::patch('restore/{id}', [NewsCategoryController::class, 'restore']); 
});

//api tin tức
Route::apiResource('news', NewsController::class);
Route::prefix('news')->group(function () {
    Route::patch('softDelete/{id}', [NewsController::class, 'softDelete']); 
    Route::patch('restore/{id}', [NewsController::class, 'restore']); 
});

//comments và Ratings
Route::prefix('products/{productId}')->group(function () {
    Route::post('comments', [CommentAndRatingController::class, 'comments']);
    Route::get('comments', [CommentAndRatingController::class, 'getComments']);
    Route::post('comments/{commentId}/hide', [CommentAndRatingController::class, 'hideComments']);
    Route::post('comments/{commentId}/unhide', [CommentAndRatingController::class, 'unhideComments']);
    
    Route::post('ratings', [CommentAndRatingController::class, 'ratings']);
    Route::get('ratings', [CommentAndRatingController::class, 'getRatings']);
    Route::post('ratings/{ratingId}/hide', [CommentAndRatingController::class, 'hideRatings']);
    Route::post('ratings/{ratingId}/unhide', [CommentAndRatingController::class, 'UnhideRatings']);
});


// phân quyền
Route::patch('restore/{id}', [ProductCategoryController::class, 'restore']);


// end admin



// client
// api sản phẩm
Route::get('/products/{slug}', [ClientProductController::class, 'showBySlug']);
Route::get('/products', [ClientProductController::class, 'productList']);
Route::get('/searchProducts', [ClientProductController::class, 'search']);
Route::get('/newProduct', [ClientProductController::class, 'newProducts']);
Route::get('/hotProduct', [ClientProductController::class, 'hotProducts']);
Route::get('/categories/{slug}', [ClientProductController::class, 'getProductsByCategorySlug']);
Route::get('/categoryId/{id}', [ClientProductController::class, 'getProductsByCategoryId']);
Route::get('/categoryParent', [ClientProductController::class, 'categoryParent']);
Route::get('/categories', [ClientProductController::class, 'getCategories']);
Route::get('/brands', [ClientProductController::class, 'getBrands']);


// cần xác thực
Route::middleware(['jwt.auth'])->group(function () {
    // api giỏ hàng
    Route::get('/carts', [CartController::class, 'showCart']);
    Route::post('/addToCart', [CartController::class, 'addToCart']);
    Route::delete('/carts/delete/{cartId}', [CartController::class, 'deleteCartItem']);
    Route::patch('/carts/updateQuantity', [CartController::class, 'updateQuantity']);

    // api đơn hàng
    Route::post('/order', [ClientOrderController::class, 'placeOrder']);
    Route::get('/order/{orderId}', [ClientOrderController::class, 'getOrderDetail']);
    Route::get('/orders', [ClientOrderController::class, 'index']);
});
Route::post('/payment/vnpay', [VNPayController::class, 'createPayment']);
Route::get('/payment/vnpay-return', [VNPayController::class, 'vnpayReturn']);


// api user
// không cần token
Route::post('/user/register', [ClientUerController::class, 'register']);
Route::post('/user/login', [ClientUerController::class, 'login']);
Route::post('/user/forgotPassword', [ClientUerController::class, 'forgotPassword']);
Route::post('/user/verify-otp', [ClientUerController::class, 'verifyOtp']);

// cần token
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/user/changePassword', [ClientUerController::class, 'changePassword']);
    Route::get('/user/profile', [ClientUerController::class, 'profile']);
    Route::patch('/user/updateProfile', [ClientUerController::class, 'updateProfile']);
});
// end client


Route::get('/test-vnpay-hash', function (Request $request) {
    $vnp_HashSecret = env('VNPAY_HASH_SECRET');

    // Nhận các tham số từ request (bỏ vnp_SecureHash nếu có)
    $inputData = $request->except(['vnp_SecureHash']);

    // Sắp xếp tham số theo thứ tự alphabet
    ksort($inputData);
    
    // Tạo chuỗi dữ liệu để hash
    $hashdata = "";
    foreach ($inputData as $key => $value) {
        $hashdata .= ($hashdata == "" ? "" : "&") . $key . "=" . $value;
    }

    // Tạo chữ ký SHA512
    $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

    return response()->json([
        'generated_hash' => $vnp_SecureHash,
        'input_data' => $inputData
    ]);
});