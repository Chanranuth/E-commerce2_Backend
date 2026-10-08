<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellerRequest;

class SellerRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SellerRequest::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'ADMIN') {
            return response()->json([
                'message' => 'You are an admin. You do not need to request seller access.'
            ], 403);
        }

        if ($user->role === 'SELLER') {
            return response()->json([
                'message' => 'You are already a seller.'
            ], 403);
        }

        $existingRequest = SellerRequest::where('user_id', $user->id)
            ->where('status', 'PENDING')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'message' => 'You already have a pending seller request.'
            ], 409);
        }

        $sellerRequest = SellerRequest::create([
            'user_id' => $user->id,
            'status' => 'PENDING',
        ]);

        return $sellerRequest;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:APPROVED,REJECTED',
        ]);

        $sellerRequest = SellerRequest::findOrFail($id);

        $sellerRequest->update([
            'status' => $request->status,
        ]);

        if ($request->status === 'APPROVED' && $sellerRequest->user->role === 'CUSTOMER') {
            $sellerRequest->user->update([
                'role' => 'SELLER',
            ]);
        }

        return $sellerRequest;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
