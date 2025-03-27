<?php

namespace App\Http\Controllers\admin;

use App\Models\Brands;
use App\Models\Order;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\UserClient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getDashboardData()
    {
        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách thống kê',
            'data' => [
                'total_revenue' => Order::sum('totalPrice'),
                'total_products' => Products::count(),
                'total_brands' => Brands::count(),
                'total_categories' => ProductCategory::count(),
                'total_orders' => Order::count(),
                'total_voucher_codes' => Voucher::count(), 
                // 'total_posts' => Post::count(),  // Tổng bài viết
                'total_users' => UserClient::count()
            ]
        ]);
    }

    // /thống kê đơn hàng và doanh thu theo ngày
    public function revenueStatistics(Request $request)
    {
        $days = $request->query('days', 7); // Lấy số ngày, mặc định là 7 ngày gần nhất

        $statistics = Order::select(
            DB::raw('DATE(createdAt) as date'),
            DB::raw('SUM(totalPrice) as revenue'),
            DB::raw('COUNT(id) as orders_count')
        )
            ->where('createdAt', '>=', Carbon::now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        return response()->json([
            'code' => 'success',
            'message' => 'Biểu đồ thống kê',
            'data' => $statistics
        ], 200);
    }

    // thống kê số lượng người đăng ký theo ngày
    public function userRegistrationStatistics(Request $request)
    {
        $days = $request->query('days', 7); // Lấy số ngày, mặc định là 7 ngày gần nhất

        $statistics = UserClient::select(
            DB::raw('DATE(createdAt) as date'),
            DB::raw('COUNT(id) as userCount')
        )
            ->where('createdAt', '>=', Carbon::now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        return response()->json([
            'code' => 'success',
            'message' => 'Thống kê số lượng người dùng đăng ký theo ngày',
            'data' => $statistics
        ], 200);
    }

    // thống kê số lượng đơn hàng theo trạng thái
    public function orderStatusStatistics()
{
    $statuses = ['pending', 'confirmed', 'shipped', 'completed', 'canceled'];

    $statistics = Order::select(
            'status',
            DB::raw('COUNT(*) as count')
        )
        ->whereIn('status', $statuses)
        ->groupBy('status')
        ->get();

    return response()->json([
        'code' => 'success',
        'message' => 'Thống kê số lượng đơn hàng theo trạng thái',
        'data' => $statistics
    ], 200);
}

}
