<?php

namespace App\Http\Controllers;

use App\Models\NewsCategory;
use Illuminate\Http\Request;

class NewsCategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $categoriesNews = NewsCategory::select('id', 'name', 'image', 'description', 'status', 'parentID', 'position', 'slug')
            ->where('deleted', false)
            ->orderBy('position', 'desc')
            ->paginate($perPage);
            // ->get();
        
        // lấy danh mục cha
        // $categories = ProductCategory::whereNull('parentID')
        // ->with('children') // Lấy luôn danh mục con
        // ->get(); 
        return response()->json([
            'code' => 'success',
            'message' => 'Danh sách danh mục sản phẩm.',
            'data' => $categoriesNews
        ], 200);
    }
}
