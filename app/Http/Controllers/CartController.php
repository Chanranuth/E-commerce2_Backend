<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Cart::with('items')->where('customer_id', request()->user()->id)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if($request->user()->role === 'ADMIN') {
            return response()->json([
                'message' => 'Admins cannot create customer carts'
            ], 403);
        }

        $cart = Cart::firstorCreate([
            'customer_id' => $request->user()->id,
        ]);

        return $cart;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cart = Cart::with('items')->findOrFail($id);

        if ($cart->customer_id !== request()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return $cart;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
