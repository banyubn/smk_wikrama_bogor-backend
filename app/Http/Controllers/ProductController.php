<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // Get products
    public function index()
    {
        $product = Product::all();

        return response()->json([
            'message' => 'Products retrieved successfully',
            'data' => $product
        ], 200);
    }

    // Create a new product
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'image' => 'required',
            'description' => 'required|min:3',
            'price' => 'required|numeric',
            'stock' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $filepath = 'products/' . $filename;

            $file->move(public_path('products'), $filename);
        }

        $product = Product::create([
            'name' => $request->name,
            'seller_id' => auth()->id(),
            'image' => $filepath,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 200);
    }

    // Update existing product
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|min:3',
            'image' => 'nullable',
            'description' => 'nullable|min:3',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|numeric'
        ]);

        $product = Product::findOrFail($id);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $filepath = 'products/' . $filename;

            $file->move(public_path('products'), $filename);
        }

        $product->update([
            'name' => $request->name ?? $product->name,
            'image' => $filepath ?? $product->image,
            'description' => $request->description ?? $product->description,
            'price' => $request->price ?? $product->price,
            'stock' => $request->stock ?? $product->stock,
        ]);

        return response()->json([
            'message' => 'Product updated successsfully',
            'data' => $product
        ], 200);
    }

    // Delete existing product
    public function delete($id)
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }
}
