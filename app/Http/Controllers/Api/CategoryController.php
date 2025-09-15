<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function show($id)
    {
        $category = Category::with('products')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }
}
