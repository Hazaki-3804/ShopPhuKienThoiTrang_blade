<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show($orderId)
    {
        $order = Order::with(['order_items.product', 'user'])->findOrFail($orderId);
        
        return view('invoice.show', compact('order'));
    }
}
