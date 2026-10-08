<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Product;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return CartItem::with(['cart', 'product'])
            ->whereHas('cart', function ($query) use ($request) {
                $query->where('customer_id', $request->user()->id);
            })
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if(!$product->is_active){
            return response()->json([
                'message' => 'Product is not available'
            ]);
        }

        if($request->quantity > $product->stock){
            return response()->json([
                'message' => 'Requested quantity exceeds available stock'
            ]);
        }

        if($product->seller_id === $request->user()->id){
            return response()->json([
                'message' => 'You cannot add your own product to the cart'
            ]);
        }

        $cart = Cart::where(
            'customer_id',
            $request->user()->id
        )->firstOrFail();

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;

            if ($newQuantity > $product->stock) {
                return response()->json([
                    'message' => 'Not enough stock available'
                ], 400);
            }

            $cartItem->quantity = $newQuantity;
            $cartItem->save();

            return $cartItem;
        }

        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

        return $cartItem;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cartItem = CartItem::with(['cart', 'product'])
            ->findOrFail($id);

        if ($cartItem->cart->customer_id !== request()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return $cartItem;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::with(['cart', 'product'])->findOrFail($id);

        if ($cartItem->cart->customer_id !== request()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!$cartItem->product->is_active) {
            return response()->json([
                'message' => 'Product is not available'
            ], 400);
        }

        if ($request->quantity > $cartItem->product->stock) {
            return response()->json([
                'message' => 'Requested quantity exceeds available stock'
            ], 400);
        }

        $cartItem->update([
            'quantity' => $request->quantity,
        ]);

        return $cartItem;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cartItem = CartItem::with('cart')->findOrFail($id);

        if ($cartItem->cart->customer_id !== request()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Cart item deleted successfully'
        ]);
    }
}
