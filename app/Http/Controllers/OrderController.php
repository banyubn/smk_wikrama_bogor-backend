<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // Get orders
    public function index()
    {
        $order = Order::all();

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'data' => $order->load('orderProducts'),
        ], 200);
    }

    public function detail($id)
    {
        $order = Order::findOrFail($id);

        return response()->json([
            'message' => 'Order retrieved successfully',
            'data' => $order->load('orderProducts'),
        ], 200);
    }

    // Create order
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::create([
            'buyer_id' => auth()->id(),
            'total_amount' => 0
        ]);

        $totalAmount = 0;

        foreach ($request->products as $item) {
            $product = Product::findOrFail($item['product_id']);
            $quantity = $item['quantity'];

            $subAmount = $product->price * $quantity;

            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'sub_amount' => $subAmount
            ]);

            $totalAmount += $subAmount;
        }

        $order->total_amount = $totalAmount;
        $order->save();

        return response()->json([
            'message' => 'Order created successfully',
            'data' => $order->load('orderProducts')
        ], 200);
    }

    // Update order status
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::findOrFail($id);

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Order status updated',
            'data' => $order,
        ], 200);
    }

    // Delete existing order
    public function delete($id)
    {
        $order = Order::findOrFail($id);

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
        ], 200);
    }
}
