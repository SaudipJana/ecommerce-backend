<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class OrderController extends Controller
{
    public function saveOrder(Request $request){
        if(!empty($request->cart)){
            $order = new Order();
            $order->name = $request->name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->address = $request->address;
            $order->city = $request->city;
            $order->state = $request->state;
            $order->zip = $request->zip;
            $order->subtotal = $request->subtotal;
            $order->grandtotal = $request->grandtotal;
            $order->shipping = $request->shipping;
            $order->discount = $request->discount;
            $order->payment_status = $request->payment_status;
            $order->payment_method = $request->payment_method;
            $order->status = $request->status;
            $order->user_id = $request->user()->id;
            $order->save();

            
            foreach($request->cart as $item){
                $orderItem = new OrderItem();
                $orderItem->name = $item['title'];
                $orderItem->size = $item['size'];
                $orderItem->price = $item['qty'] * $item['price'];
                $orderItem->unit_price = $item['price'];
                $orderItem->qty = $item['qty'];
                $orderItem->product_id = $item['product_id'];
                $orderItem->order_id = $order->id;
                $orderItem->save();
            }

            return response()->json([
                'status' => 200,
                'id' => $order->id,
                'message' => 'Order Placed Successfully'
            ], 200);
        }else{
             return response()->json([
                'status' => 400,
                'message' => 'Your Cart Is Empty'
            ], 400);
        }
        
    }

    public function createPaymentIntent(Request $request){
        try {
            if($request->amount > 0){
                Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

                $paymentIntent = PaymentIntent::create([
                    'amount' => $request->amount,
                    'currency' => 'USD',
                    'payment_method_types' => ['card']
                ]);

                $clientSecret = $paymentIntent->client_secret;

                return response()->json([
                    'status' => 200,
                    'clientSecret' => $clientSecret
                ], 200);

            }else{
                return response()->json([
                    'status' => 400,
                    'message' => 'Amount must be grater than 0'
                ], 400);
            }


        } catch (\Exception $e) {
            return response()->json([
                    'status' => 500,
                    'message' => $e->getMessage()
                ], 500);
        }
    }
}
