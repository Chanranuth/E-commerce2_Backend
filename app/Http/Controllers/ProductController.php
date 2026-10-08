<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::where('is_active', 1)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'sku' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        if ($request->user()->role !== 'SELLER') {
            return response()->json([
                'message' => 'Only sellers can create products'
            ], 403);
        }

        $product = Product::create([
            'seller_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'sku' => $request->sku,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'is_active' => false,
        ]);

        return $product;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Product::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'sku' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($id);

        if ($product->seller_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You can only update your own products'
            ], 403);
        }

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'sku' => $request->sku,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);

        return $product;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        if ($product->seller_id !== request()->user()->id) {
            return response()->json([
                'message' => 'You can only delete your own products'
            ], 403);
        }

        $product->update([
            'is_active' => 0,
        ]);

        return $product;
    }

    public function moderate(Request $request, string $id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $product = Product::findOrFail($id);

        if ($request->user()->role !== 'ADMIN') {
            return response()->json([
                'message' => 'Only admins can moderate products'
            ], 403);
        }

        $product->update([
            'is_active' => $request->is_active,
        ]);

        return $product;
    }
}
