<?php

namespace App\Http\Controllers\admin;

use App\Models\Brands;
use App\Models\news;
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
                'total_revenue' => Order::where('status', '!=', 'canceled')->sum('totalPrice'),
                'total_products' => Products::count(),
                'total_brands' => Brands::count(),
                'total_categories' => ProductCategory::count(),
                'total_orders' => Order::count(),
                'total_voucher_codes' => Voucher::count(),
                'total_posts' => news::count(),
                'total_users' => UserClient::count()
            ]
        ]);
    }

    // /thống kê đơn hàng và doanh thu theo ngày thắng nằm
    // public function revenueStatistics(Request $request)
    // {
    //     $days = $request->query('days', 7); // Lấy số ngày, mặc định là 7 ngày gần nhất

    //     $statistics = Order::select(
    //         DB::raw('DATE(createdAt) as date'),
    //         DB::raw('SUM(totalPrice) as revenue'),
    //         DB::raw('COUNT(id) as orders_count')
    //     )
    //         ->where('createdAt', '>=', Carbon::now()->subDays($days))
    //         ->groupBy('date')
    //         ->orderBy('date', 'ASC')
    //         ->get();

    //     return response()->json([
    //         'code' => 'success',
    //         'message' => 'Biểu đồ thống kê',
    //         'data' => $statistics
    //     ], 200);
    // }

    public function revenueStatistics(Request $request)
    {
        $type = $request->query('type', 'day');
        $query = Order::query()
            ->where('status', '!=', 'canceled');

        switch ($type) {
            case 'day':
                $startDate = Carbon::now()->subDays(7);
                $query->select(
                    DB::raw('DATE(createdAt) as label'),
                    DB::raw('SUM(totalPrice) as revenue'),
                    DB::raw('COUNT(id) as orders_count')
                )->where('createdAt', '>=', $startDate)
                    ->groupBy('label')
                    ->orderBy('label');
                break;

            case 'month':
                $startMonth = Carbon::now()->subMonths(12);
                $query->select(
                    DB::raw('DATE_FORMAT(createdAt, "%Y-%m") as label'),
                    DB::raw('SUM(totalPrice) as revenue'),
                    DB::raw('COUNT(id) as orders_count')
                )->where('createdAt', '>=', $startMonth)
                    ->groupBy('label')
                    ->orderBy('label');
                break;

            case 'year':
                $query->select(
                    DB::raw('YEAR(createdAt) as label'),
                    DB::raw('SUM(totalPrice) as revenue'),
                    DB::raw('COUNT(id) as orders_count')
                )->groupBy('label')
                    ->orderBy('label');
                break;
        }

        $statistics = $query->get();

        return response()->json([
            'code' => 'success',
            'message' => 'Biểu đồ thống kê',
            'data' => $statistics
        ]);
    }


    // thống kê số lượng người đăng ký theo ngày tháng năm
    public function userRegistrationStatistics(Request $request)
    {
        $type = $request->query('type', 'day'); // "day", "month", "year"
    
        $query = UserClient::query();
    
        switch ($type) {
            case 'day':
                $startDate = Carbon::now()->subDays(7);
                $query->select(
                    DB::raw('DATE(createdAt) as label'),
                    DB::raw('COUNT(id) as userCount')
                )
                ->where('createdAt', '>=', $startDate)
                ->groupBy('label')
                ->orderBy('label');
                break;
    
            case 'month':
                $startMonth = Carbon::now()->subMonths(12);
                $query->select(
                    DB::raw('DATE_FORMAT(createdAt, "%Y-%m") as label'),
                    DB::raw('COUNT(id) as userCount')
                )
                ->where('createdAt', '>=', $startMonth)
                ->groupBy('label')
                ->orderBy('label');
                break;
    
            case 'year':
                $query->select(
                    DB::raw('YEAR(createdAt) as label'),
                    DB::raw('COUNT(id) as userCount')
                )
                ->groupBy('label')
                ->orderBy('label');
                break;
        }
    
        $statistics = $query->get();
    
        return response()->json([
            'code' => 'success',
            'message' => 'Thống kê số lượng người dùng đăng ký',
            'data' => $statistics
        ]);
    }

    // public function userRegistrationStatistics(Request $request)
    // {
    //     $days = $request->query('days', 7); // Lấy số ngày, mặc định là 7 ngày gần nhất

    //     $statistics = UserClient::select(
    //         DB::raw('DATE(createdAt) as date'),
    //         DB::raw('COUNT(id) as userCount')
    //     )
    //         ->where('createdAt', '>=', Carbon::now()->subDays($days))
    //         ->groupBy('date')
    //         ->orderBy('date', 'ASC')
    //         ->get();

    //     return response()->json([
    //         'code' => 'success',
    //         'message' => 'Thống kê số lượng người dùng đăng ký theo ngày',
    //         'data' => $statistics
    //     ], 200);
    // }



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
