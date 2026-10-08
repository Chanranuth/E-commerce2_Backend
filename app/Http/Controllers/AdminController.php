<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;

class AdminController extends Controller
{
    public function users()
    {
        return User::select(
            'id',
            'name',
            'email',
            'role',
            'created_at'
        )->get();
    }

    public function orders()
    {
        return Order::with(['items', 'seller', 'customer'])->get();
    }
}
