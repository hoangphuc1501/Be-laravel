<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    // danh sách voucher
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $status = $request->input('status');
        $sort = $request->input('sort', 'createdAt-Desc');

        $query = Voucher::select(
            'id',
            'name',
            'code',
            'discountType',
            'discountValue',
            'description',
            'status',
            'minOrderValue',
            'maxOrderValue',
            'maxDiscount',
            'startDate',
            'endDate',
            'usageLimit',
            'numberOfUses'
        )
            ->where('deleted', false);

        // Tìm kiếm theo tên hoặc mã voucher
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%");
            });
        }

        // Lọc theo trạng thái
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Sắp xếp
        switch ($sort) {
            case 'createdAt-asc':
                $query->orderBy('createdAt', 'asc');
                break;
            case 'title-desc':
                $query->orderBy('name', 'desc');
                break;
            case 'title-asc':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->orderBy('createdAt', 'desc');
                break;
        }

        $vouchers = $query->paginate($perPage);

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách voucher.',
            'data' => $vouchers
        ], 200);
    }


    // thêm mới voucher
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|max:100|unique:vouchers,code',
            'discountType' => ['required', 'integer', Rule::in([1, 2])], // 1: %, 2: tiền
            'discountValue' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'minOrderValue' => 'nullable|integer|min:0',
            'maxOrderValue' => 'nullable|integer|min:0',
            'maxDiscount' => 'nullable|integer|min:0',
            'startDate' => 'required|date|after_or_equal:today',
            'endDate' => 'required|date|after:startDate',
            'usageLimit' => 'nullable|integer|min:1',
        ]);

        $voucher = Voucher::create([
            'name' => $request->name,
            'code' => $request->code,
            'discountType' => $request->discountType,
            'discountValue' => $request->discountValue,
            'description' => $request->description,
            'status' => $request->status,
            'minOrderValue' => $request->minOrderValue,
            'maxOrderValue' => $request->maxOrderValue,
            'maxDiscount' => $request->maxDiscount,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'usageLimit' => $request->usageLimit,
            'numberOfUses' => 0,
            'deleted' => 0,
        ]);

        return response()->json([
            'code' => 'success',
            'message' => 'Tạo voucher thành công.',
            'data' => $voucher
        ], 201);
    }

    // chi tiết voucher
    public function show($id)
    {
        $voucher = Voucher::find($id);

        if (!$voucher || $voucher->deleted) {
            return response()->json([
                'code' => 'error',
                'message' => 'Voucher không tồn tại hoặc đã bị xóa.'
            ], 404);
        }

        return response()->json([
            'code' => 'success',
            'message' => 'Chi tiết voucher.',
            'data' => $voucher
        ], 200);
    }

    //  Cập nhật voucher
    public function update(Request $request, $id)
    {
        $voucher = Voucher::find($id);

        if (!$voucher || $voucher->deleted) {
            return response()->json([
                'code' => 'error',
                'message' => 'Voucher không tồn tại hoặc đã bị xóa.'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string',
            'code' => ['required', 'string', 'max:100', Rule::unique('vouchers')->ignore($voucher->id)],
            'discountType' => ['required', 'integer', Rule::in([1, 2])],
            'discountValue' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'minOrderValue' => 'nullable|integer|min:0',
            'maxOrderValue' => 'nullable|integer|min:0',
            'maxDiscount' => 'nullable|integer|min:0',
            'startDate' => 'required|date|after_or_equal:today',
            'endDate' => 'required|date|after:startDate',
            'usageLimit' => 'nullable|integer|min:1',
        ]);

        $voucher->update($request->all());

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật voucher thành công.',
            'data' => $voucher
        ], 200);
    }

    // api xóa vĩnh viễn
    public function destroy($id)
    {
        $voucher = Voucher::find($id);

        if (!$voucher || !$voucher->deleted) {
            return response()->json([
                'code' => 'error',
                'message' => 'Voucher không tồn tại hoặc chưa bị xóa.'
            ], 404);
        }

        $voucher->delete();

        return response()->json([
            'code' => 'success',
            'message' => 'Voucher đã bị xóa vĩnh viễn.'
        ], 200);
    }

    // api xóa mềm
    public function softDelete($id)
    {
        $voucher = Voucher::find($id);

        if (!$voucher || $voucher->deleted) {
            return response()->json([
                'code' => 'error',
                'message' => 'Voucher không tồn tại hoặc đã bị xóa.'
            ], 404);
        }

        $voucher->update([
            'deleted' => 1,
            'deletedAt' => now()
        ]);

        return response()->json([
            'code' => 'success',
            'message' => 'Voucher đã được xóa.',
            'data' => $voucher
        ], 200);
    }

    // api khôi phục
    public function restore($id)
    {
        $voucher = Voucher::find($id);

        if (!$voucher || !$voucher->deleted) {
            return response()->json([
                'code' => 'error',
                'message' => 'Voucher không tồn tại hoặc chưa bị xóa.'
            ], 404);
        }

        $voucher->update([
            'deleted' => 0,
            'deletedAt' => null
        ]);

        return response()->json([
            'code' => 'success',
            'message' => 'Voucher đã được khôi phục.',
            'data' => $voucher
        ], 200);
    }

    // cập nhật trạng thái
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $voucher = Voucher::find($id);
        if (!$voucher) {
            return response()->json([
                'code' => 'error',
                'message' => 'voucher không tồn tại!'
            ], 404);
        }

        $voucher->status = $request->status;
        $voucher->save();

        return response()->json([
            'code' => 'success',
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => $voucher
        ]);
    }

    // chỉ 1 voucher
    public function validateVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'totalPrice' => 'required|integer|min:0'
        ]);

        $voucher = Voucher::where('code', $request->code)
            ->where('status', 'active')
            ->where('deleted', false)
            ->first();

        if (!$voucher) {
            return response()->json([
                'code' => 'error',
                'message' => 'Voucher không tồn tại hoặc đã bị vô hiệu hóa.'
            ], 400);
        }

        // Kiểm tra ngày hợp lệ
        $now = now();
        $startDate = \Carbon\Carbon::parse($voucher->startDate);
        $endDate = \Carbon\Carbon::parse($voucher->endDate);

        // Debug log
        // Log::info("🔍 Kiểm tra voucher: ", [
        //     'now' => $now->toDateTimeString(),
        //     'startDate' => $startDate->toDateTimeString(),
        //     'endDate' => $endDate->toDateTimeString()
        // ]);

        if ($now->lt($startDate) || $now->gt($endDate)) {
            return response()->json([
                'code' => 'error',
                'message' => 'Voucher đã hết hạn.'
            ], 400);
        }

        // Kiểm tra số lần sử dụng
        if ($voucher->usageLimit !== null && $voucher->numberOfUses >= $voucher->usageLimit) {
            return response()->json([
                'code' => 'error',
                'message' => 'Voucher đã hết số lần sử dụng.'
            ], 400);
        }

        // Kiểm tra giá trị đơn hàng
        if ($request->totalPrice < $voucher->minOrderValue) {
            return response()->json([
                'code' => 'error',
                'message' => 'Đơn hàng không đủ điều kiện áp dụng voucher.'
            ], 400);
        }

        // Tính giảm giá
        $discountAmount = ($voucher->discountType == 1)
            ? min($request->totalPrice * ($voucher->discountValue / 100), $voucher->maxDiscount)
            : min($voucher->discountValue, $voucher->maxDiscount ?? $voucher->discountValue);

        return response()->json([
            'code' => 'success',
            'message' => 'Voucher hợp lệ.',
            'discountAmount' => $discountAmount,
            'voucher' => [
                'code' => $voucher->code,
                'discountAmount' => $discountAmount,
            ]
        ], 200);
    }


    public function getVoucherClient(Request $request)
    {
        $perPage = $request->input('per_page', 6);
        $filterType = $request->input('filter_type');
        $page = $request->input('page', 1);
        $query = Voucher::select(
            'id',
            'name',
            'code',
            'discountType',
            'discountValue',
            'description',
            'status',
            'minOrderValue',
            'startDate',
            'endDate',
            'usageLimit',
            'numberOfUses'
        )
            ->where('deleted', false)
            ->where('status', "active");

        // Áp dụng bộ lọc nếu có
        if ($filterType == 'percent') {
            $query->where('discountType', 1);
        } elseif ($filterType == 'money') {
            $query->where('discountType', 2);
        }

        // $vouchers = $query->orderBy('createdAt', 'desc')->paginate($perPage);
        $vouchers = $query->orderBy('createdAt', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách voucher.',
            'data' => $vouchers
        ], 200);
    }

    public function trashVoucher(Request $request)
    {
        $perPage = $request->input('per_page', 10);
    
        $vouchers = Voucher::select(
            'id',
            'name',
            'code',
            'discountType',
            'discountValue',
            'description',
            'status',
            'minOrderValue',
            'maxOrderValue',
            'maxDiscount',
            'startDate',
            'endDate',
            'usageLimit',
            'numberOfUses'
        )
            ->where('deleted', true)
            ->orderBy('createdAt', 'desc')
            ->paginate($perPage);
    
        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách voucher.',
            'data' => $vouchers
        ], 200);
    }


    // nhìu voucher nhưng chỉ tối đa 3 cái
//     public function validateVoucher(Request $request)
// {
//     $request->validate([
//         'codes' => 'required|array|max:3', // Nhận tối đa 3 voucher
//         'codes.*' => 'string', // Mỗi phần tử trong mảng là string
//         'totalPrice' => 'required|integer|min:0'
//     ]);

    //     $totalDiscount = 0;
//     $appliedVouchers = [];

    //     foreach ($request->codes as $code) {
//         $voucher = Voucher::where('code', $code)
//             ->where('status', 'active')
//             ->where('deleted', false)
//             ->first();

    //         if (!$voucher) {
//             return response()->json([
//                 'code' => 'error',
//                 'message' => "Voucher '{$code}' không hợp lệ hoặc đã hết hạn."
//             ], 400);
//         }

    //         $now = now();
//         $startDate = \Carbon\Carbon::parse($voucher->startDate);
//         $endDate = \Carbon\Carbon::parse($voucher->endDate);

    //         if ($now->lt($startDate) || $now->gt($endDate)) {
//             return response()->json([
//                 'code' => 'error',
//                 'message' => "Voucher '{$code}' đã hết hạn hoặc chưa bắt đầu."
//             ], 400);
//         }

    //         if ($voucher->usageLimit !== null && $voucher->numberOfUses >= $voucher->usageLimit) {
//             return response()->json([
//                 'code' => 'error',
//                 'message' => "Voucher '{$code}' đã hết số lần sử dụng."
//             ], 400);
//         }

    //         if ($request->totalPrice < $voucher->minOrderValue) {
//             return response()->json([
//                 'code' => 'error',
//                 'message' => "Voucher '{$code}' yêu cầu đơn hàng tối thiểu {$voucher->minOrderValue}."
//             ], 400);
//         }

    //         // Tính giảm giá
//         $discountAmount = ($voucher->discountType == 1)
//             ? min($request->totalPrice * ($voucher->discountValue / 100), $voucher->maxDiscount)
//             : min($voucher->discountValue, $voucher->maxDiscount ?? $voucher->discountValue);

    //         $totalDiscount += $discountAmount;
//         $appliedVouchers[] = [
//             'code' => $code,
//             'discountAmount' => $discountAmount
//         ];
//     }

    //     return response()->json([
//         'code' => 'success',
//         'message' => 'Voucher hợp lệ.',
//         'totalDiscount' => $totalDiscount,
//         'appliedVouchers' => $appliedVouchers
//     ], 200);
// }


}
