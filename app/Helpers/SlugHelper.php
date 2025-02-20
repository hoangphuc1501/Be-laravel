<?php

use Illuminate\Support\Str;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Model;

// hàm tạo slug duy nhất
if (!function_exists('generateUniqueSlug')) {
    function generateUniqueSlug($name, $model)
    {
        $slug = Str::slug($name); // Chuyển đổi tên thành slug
        $originalSlug = $slug; // Lưu lại slug gốc
        $count = 1;

        // Kiểm tra xem slug đã tồn tại chưa
        // while (ProductCategory::where('slug', $slug)->exists()) {
        //     $slug = $originalSlug . '-' . $count;
        //     $count++;
        // }
        // Kiểm tra xem slug đã tồn tại trong bảng được chỉ định chưa
        while ($model::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
