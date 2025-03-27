<?php

// use App\Http\Controllers\BrandController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\admin\ContactController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\NewsCategoryController;
use App\Http\Controllers\admin\NewsController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\PermissionController;
use App\Http\Controllers\admin\RoleController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\client\CartController;
use App\Http\Controllers\client\ClientUerController;
use App\Http\Controllers\client\ClientOrderController;
use App\Http\Controllers\Admin\ProductVariantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\admin\ReviewController;
use App\Http\Controllers\admin\RolePermissionController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\VoucherController;
use App\Http\Controllers\client\ClientProductController;
use App\Http\Controllers\client\CommentController;
use App\Http\Controllers\client\FavoriteController;
use App\Http\Controllers\VNPayController;
use App\Http\Controllers\ZaloPayController;

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
Route::prefix('admin')->middleware(['auth:client_api', 'admin'])->group(function () {
    // api sản phẩm
    Route::apiResource('products', ProductController::class);
    Route::prefix('products')->group(function () {
        Route::patch('softDelete/{id}', [ProductController::class, 'softDelete']);
        Route::patch('restore/{id}', [ProductController::class, 'restore']);
    });
    Route::patch('/products/{id}/status', [ProductController::class, 'updateStatus']);
    Route::patch('/products/{id}/featured', [ProductController::class, 'updateFeature']);
    Route::patch('/products/{id}/position', [ProductController::class, 'updatePosition']);
    
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
    Route::patch('/colors/{id}/status', [ColorController::class, 'updateStatus']);
    Route::patch('/colors/{id}/position', [ColorController::class, 'updatePosition']);

    // api size
    Route::apiResource('sizes', SizeController::class);
    Route::prefix('sizes')->group(function () {
        Route::patch('/softDelete/{id}', [SizeController::class, 'softDelete']);
        Route::patch('/restore/{id}', [SizeController::class, 'restore']);
    });
    Route::get('listSize', [SizeController::class, 'listSize']);
    Route::patch('/sizes/{id}/status', [SizeController::class, 'updateStatus']);
    Route::patch('/sizes/{id}/position', [SizeController::class, 'updatePosition']);

    // api thương hiệu
    Route::apiResource('brands', BrandController::class);
    Route::prefix('brands')->group(function () {
        Route::patch('/softDelete/{id}', [BrandController::class, 'softDelete']);
        Route::patch('/restore/{id}', [BrandController::class, 'restore']);
    });
    Route::get('ListBrands', [BrandController::class, 'listBrands']);
    Route::patch('/brands/{id}/status', [BrandController::class, 'updateStatus']);
    Route::patch('/brands/{id}/position', [BrandController::class, 'updatePosition']);

    // api danh mục sản phẩm
    Route::apiResource('productcategories', ProductCategoryController::class);
    Route::prefix('productcategories')->group(function () {
        Route::patch('softDelete/{id}', [ProductCategoryController::class, 'softDelete']);
        Route::patch('restore/{id}', [ProductCategoryController::class, 'restore']);
    });
    Route::get('ListCategory', [ProductCategoryController::class, 'ListCategory']);
    Route::patch('/productcategories/{id}/status', [ProductCategoryController::class, 'updateStatus']);
    Route::patch('/productcategories/{id}/position', [ProductCategoryController::class, 'updatePosition']);

    // đơn hàng
    Route::get('/orders', [OrderController::class, 'index']);
    Route::patch('/orders/{id}', [OrderController::class, 'update']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // phân quyền
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::post('role/{roleId}/permissions', [RolePermissionController::class, 'assignPermissionsToRole']);
    Route::post('user/{userId}/role', [RolePermissionController::class, 'assignRoleToUser']);
    Route::get('permissionGetAll', [PermissionController::class, 'getAllPermissions']);

    // api user
    Route::apiResource('users', UserController::class);
    Route::prefix('users')->group(function () {
        Route::patch('/softDelete/{id}', [UserController::class, 'softDelete']);
        Route::patch('/restore/{id}', [UserController::class, 'restore']);
    });
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
    Route::patch('/users/{id}/position', [UserController::class, 'updatePosition']);

    // api bình luận
    Route::get('/comments', [CommentController::class, 'getAllComments']);
    Route::delete('/comments/{id}', [CommentController::class, 'deleteCommentAdmin']);

    // api đánh giá
    Route::get('/ratings', [ReviewController::class, 'getAllReviews']);
    Route::delete('/ratings/{id}', [ReviewController::class, 'deleteReviewAdmin']);

    // api liên hệ
    Route::apiResource('contacts', ContactController::class);

    // api thống kê 
    Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);
    Route::get('/dashboard/revenue-statistics', [DashboardController::class, 'revenueStatistics']);
    Route::get('/dashboard/user-registration-statistics', [DashboardController::class, 'userRegistrationStatistics']);
    Route::get('/dashboard/order-status-statistics', [DashboardController::class, 'orderStatusStatistics']);


    // api voucher
    Route::apiResource('vouchers', VoucherController::class);
    Route::prefix('vouchers')->group(function () {
        Route::patch('/softDelete/{id}', [VoucherController::class, 'softDelete']);
        Route::patch('/restore/{id}', [VoucherController::class, 'restore']);
    });
    Route::patch('/vouchers/{id}/status', [VoucherController::class, 'updateStatus']);

    // api danh mục tin tức
    Route::apiResource('newscategories', NewsCategoryController::class);
    Route::prefix('newscategories')->group(function () {
        Route::patch('softDelete/{id}', [NewsCategoryController::class, 'softDelete']);
        Route::patch('restore/{id}', [NewsCategoryController::class, 'restore']);
    });
    Route::get('ListCategory-news', [NewsCategoryController::class, 'ListCategory']);
    Route::patch('/newscategories/{id}/status', [NewsCategoryController::class, 'updateStatus']);
    Route::patch('/newscategories/{id}/position', [NewsCategoryController::class, 'updatePosition']);

    // api tin tức
    Route::apiResource('news', NewsController::class);
    Route::prefix('news')->group(function () {
        Route::patch('softDelete/{id}', [NewsController::class, 'softDelete']);
        Route::patch('restore/{id}', [NewsController::class, 'restore']);
    });
    Route::patch('/news/{id}/status', [NewsController::class, 'updateStatus']);
    Route::patch('/news/{id}/featured', [NewsController::class, 'updateFeature']);
    Route::patch('/news/{id}/position', [NewsController::class, 'updatePosition']);


    // thùng rác
Route::get('/trash/productCategory', [ProductCategoryController::class, 'trashProductCategory']);
Route::get('/trash/products', [ProductController::class, 'trashProduct']);
Route::get('/trash/brands', [BrandController::class, 'trashBrand']);
Route::get('/trash/colors', [ColorController::class, 'trashColor']);
Route::get('/trash/sizes', [SizeController::class, 'trashSize']);
Route::get('/trash/vouchers', [VoucherController::class, 'trashVoucher']);
Route::get('/trash/newsCategory', [NewsCategoryController::class, 'trashNewsCategory']);
Route::get('/trash/news', [NewsController::class, 'trashNews']);
// end thùng rác
});

// api thông số kỹ  thuật
// Route::apiResource('productspecifications', ProductSpecificationController::class);
// Route::prefix('productspecifications')->group(function () {
//     Route::patch('/softDelete/{id}', [ProductSpecificationController::class, 'softDelete']);
//     Route::patch('/restore/{id}', [ProductSpecificationController::class, 'restore']);
// });


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

    // api yêu thích
    Route::post('/favorites/add', [FavoriteController::class, 'addToFavorite']);
    Route::get('/favorites', [FavoriteController::class, 'showFavorites']);
    Route::delete('/favorites/{id}', [FavoriteController::class, 'deleteFavoriteItem']);

    // api đơn hàng
    Route::post('/order', [ClientOrderController::class, 'placeOrder']);
    Route::get('/order/{orderId}', [ClientOrderController::class, 'getOrderDetail']);
    Route::get('/orders', [ClientOrderController::class, 'index']);
    Route::post('/order/{orderId}/cancel', [ClientOrderController::class, 'cancelOrder']);
    Route::get('/order/status', [ClientOrderController::class, 'getOrderStatus']);


    // api đánh giá
    Route::post('/reviews', [ReviewController::class, 'storeReview']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'deleteReview']);

    // Bình Luận 
    Route::post('/comments', [CommentController::class, 'storeComment']);
    Route::delete('/comments/{id}', [CommentController::class, 'deleteComment']);

    // api voucher
    Route::post('/vouchers/validate', [VoucherController::class, 'validateVoucher']);
    Route::get('/vouchers', [VoucherController::class, 'getVoucherClient']);

});

// api show bình luận đánh giá
Route::get('/comments/{productId}', [CommentController::class, 'getComments']);
Route::get('/reviews/{productId}', [ReviewController::class, 'getReviews']);

// api user
// không cần token
Route::post('/user/register', [ClientUerController::class, 'register']);
Route::post('/user/login', [ClientUerController::class, 'login']);
Route::post('/user/forgotPassword', [ClientUerController::class, 'forgotPassword']);
Route::post('/user/verify-otp', [ClientUerController::class, 'verifyOtp']);
Route::post('/loginAdmin', [ClientUerController::class, 'loginAdmin']);
// cần token
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/user/changePassword', [ClientUerController::class, 'changePassword']);
    Route::get('/user/profile', [ClientUerController::class, 'profile']);
    Route::patch('/user/updateProfile', [ClientUerController::class, 'updateProfile']);
});
// end client

// Route::post('/zalopay/create-payment', [ZaloPayController::class, 'createPayment']);
// Route::post('/zalopay/callback', [ZaloPayController::class, 'callback']);
Route::post('/zalopay/create-payment', [ClientOrderController::class, 'createPayment']);
Route::post('/zalopay/callback', [ZaloPayController::class, 'callback']);
Route::post('/vnpay/create-payment', [VNPayController::class, 'createPayment']);
Route::get('/vnpay/return', [VNPayController::class, 'paymentReturn'])->name('vnpay.return');


// Route::post('/zalopay/simulate', [ZaloPayController::class, 'simulatePayment']);


