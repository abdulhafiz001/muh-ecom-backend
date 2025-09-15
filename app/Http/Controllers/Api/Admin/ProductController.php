<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function getAllProducts(Request $request)
    {
        // Get all products for admin use (no pagination)
        $products = Product::with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        // Debug: Log what we're receiving
        \Log::info('Product creation request received', [
            'has_files_images' => $request->hasFile('images'),
            'has_files_images_array' => $request->hasFile('images.*'),
            'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
            'all_data' => $request->except(['images']), // Exclude files from log
            'content_type' => $request->header('Content-Type'),
            'all_files' => array_keys($request->allFiles())
        ]);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max per image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            \Log::info('Processing image uploads', ['count' => count($images)]);
            foreach ($images as $index => $image) {
                \Log::info("Processing image $index", [
                    'original_name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'mime_type' => $image->getMimeType()
                ]);
                $path = $image->store('products', 'public');
                $fullUrl = asset('storage/' . $path);
                $imagePaths[] = $fullUrl;
                \Log::info("Image stored", ['path' => $path, 'url' => $fullUrl]);
            }
        } else {
            \Log::info('No images found in request');
        }

        \Log::info('Creating product with data', [
            'name' => $request->name,
            'images' => $imagePaths,
            'image_paths_count' => count($imagePaths)
        ]);
        
        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'stock_quantity' => $request->stock_quantity,
            'sku' => $request->sku,
            'images' => $imagePaths,
            'category_id' => $request->category_id,
            'is_active' => $request->get('is_active', true),
            'is_featured' => $request->get('is_featured', false),
        ]);
        
        \Log::info('Product created successfully', [
            'product_id' => $product->id,
            'saved_images' => $product->images
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku,' . $id,
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max per image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image uploads
        $imagePaths = $product->images ?? [];
        if ($request->hasFile('images')) {
            // Delete old images
            foreach ($imagePaths as $oldImage) {
                $oldPath = str_replace(asset('storage/'), '', $oldImage);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            
            // Upload new images
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = asset('storage/' . $path);
            }
        }

        $product->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'stock_quantity' => $request->stock_quantity,
            'sku' => $request->sku,
            'images' => $imagePaths,
            'category_id' => $request->category_id,
            'is_active' => $request->get('is_active', true),
            'is_featured' => $request->get('is_featured', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
