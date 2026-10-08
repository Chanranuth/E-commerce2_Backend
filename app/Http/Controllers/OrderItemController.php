<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderItem;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return OrderItem::whereHas('order', function ($query) {
            $query->where('customer_id', request()->user()->id);
        })->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Order items are created automatically during checkout.'
        ], 403);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $orderItem = OrderItem::with('order')->findOrFail($id);

        if ($orderItem->order->customer_id !== request()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return $orderItem;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return response()->json([
            'message' => 'Order items cannot be updated.'
        ], 403);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return response()->json([
            'message' => 'Order items cannot be deleted.'
        ], 403);
    }
}
