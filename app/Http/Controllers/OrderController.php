<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Order::where('customer_id', request()->user()->id)->with(['items', 'seller'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Orders are created automatically during checkout.'
        ], 403);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with(['items', 'seller'])->findOrFail($id);

        if ($order->customer_id !== request()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return $order;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::with('items')->findOrFail($id);

        $user = $request->user();

        if ($order->customer_id !== $request->user()->id && $order->seller_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:PAID,CANCELLED,COMPLETED',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        //CUSTOMER permissions
        if($order->customer_id === $user->id){
            
            $allowed =
                ($oldStatus === 'PENDING' && in_array($newStatus, ['PAID', 'CANCELLED'])) ||
                ($oldStatus === 'PAID' && $newStatus === 'CANCELLED');

            if (!$allowed) {
                return response()->json([
                    'message' => "Customer cannot change order status from {$oldStatus} to {$newStatus}."
                ], 403);
            }
        }

        //SELLER permissions
        if($order->seller_id === $user->id){

            $allowed =
                ($oldStatus === 'PAID' && $newStatus === 'COMPLETED');

            if (!$allowed) {
                return response()->json([
                    'message' => "Seller cannot change order status from {$oldStatus} to {$newStatus}."
                ], 403);
            }
        }

        // PENDING → PAID
        if ($oldStatus === 'PENDING' && $newStatus === 'PAID') {

            foreach ($order->items as $item) {
                $product = Product::findOrFail($item->product_id);

                if ($product->stock < $item->quantity) {
                    return response()->json([
                        'message' => "Not enough stock for {$product->name}"
                    ], 400);
                }
            }

            foreach ($order->items as $item) {
                $product = Product::findOrFail($item->product_id);

                $product->stock -= $item->quantity;
                $product->save();
            }
        }

        // PAID → CANCELLED
        if ($oldStatus === 'PAID' && $newStatus === 'CANCELLED') {

            foreach ($order->items as $item) {
                $product = Product::findOrFail($item->product_id);

                $product->stock += $item->quantity;
                $product->save();
            }
        }

        // PAID → COMPLETED
        // No stock change.

        $order->update([
            'status' => $newStatus,
        ]);

        return $order;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);

        // Only the customer who owns the order can delete it
        if ($order->customer_id !== request()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // Only PENDING orders can be deleted
        if ($order->status !== 'PENDING') {
            return response()->json([
                'message' => "Cannot delete an order with status {$order->status}."
            ], 403);
        }

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }

    public function checkout(Request $request)
    {
        $cart = Cart::with('items.product')
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty'
            ], 400);
        }

        // Validate every cart item first
        foreach ($cart->items as $item) {

            $product = $item->product;

            if (!$product->is_active) {
                return response()->json([
                    'message' => "Product {$product->name} is inactive."
                ], 400);
            }

            if ($item->quantity > $product->stock) {
                return response()->json([
                    'message' => "Not enough stock for {$product->name}."
                ], 400);
            }

            if ($product->seller_id === $request->user()->id) {
                return response()->json([
                    'message' => "You cannot buy your own product."
                ], 400);
            }
        }

        $orders = DB::transaction(function () use ($cart, $request) {

            // Group cart items by seller
            $itemsBySeller = $cart->items->groupBy(function ($item) {
                return $item->product->seller_id;
            });

            $orders = [];

            foreach ($itemsBySeller as $sellerId => $items) {

                $total = 0;

                foreach ($items as $item) {
                    $total += $item->product->price * $item->quantity;
                }

                $total = round($total, 2);

                $order = Order::create([
                    'customer_id' => $request->user()->id,
                    'seller_id' => $sellerId,
                    'total_amount' => $total,
                    'status' => 'PENDING',
                ]);

                foreach ($items as $item) {

                    $product = $item->product;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'quantity' => $item->quantity,
                        'subtotal' => round(
                            $product->price * $item->quantity,
                            2
                        ),
                    ]);
                }

                $orders[] = $order;
            }

            // Clear cart only after all orders are created
            $cart->items()->delete();

            return $orders;
        });

        return response()->json([
            'message' => 'Checkout successful',
            'orders' => $orders
        ], 201);
    }

    public function sellerOrders(Request $request)
    {
        return Order::with(['items', 'customer'])
            ->where('seller_id', $request->user()->id)
            ->get();
    }
}