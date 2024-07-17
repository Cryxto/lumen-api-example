<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\Midtrans\CreateSnapAndRedirectService;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->except('user_auth'), [
            'data.attributes.order_id' => 'required|exists:orders,id',
            'data.attributes.payment_type' => 'required'
        ]);
        // $validator->validate();
        if ($validator->fails()) {
            # code...
            // return 
            return response()->json(
                [
                    "error" => $validator->messages(),
                    "code" => 400
                ]
            );
        }

        $order_id = $request->input('data.attributes.order_id');
        $checkPayment = Payment::where('order_id', $order_id)->first();
        $orders = Order::where('id', $order_id)->first();
        // dd($order_id);
        // $getStatus = $midtransCheck->checkStatus();
        // var_dump($getStatus);
        if ($checkPayment) {
            $midtransCheck = new MidtransService($checkPayment);
            $getStatus = get_object_vars((object)$midtransCheck->checkStatus());
            if ($getStatus['transaction_status'] !== $checkPayment->transaction_status) {
                # code...
                $checkPayment->transaction_status = $getStatus['transaction_status'];
                $checkPayment->save();
                return response()->json([
                    'status' => $getStatus['transaction_status'],
                    "message"=>'status updated',
                    'code' => 200
                ])->setStatusCode(200);
            }
            return response()->json([
                'status' => $getStatus['transaction_status'],
                'code' => 200
            ])->setStatusCode(200);
        } else if(is_null($orders->id)) {
            return response()->json([
                'status' => 'order not found',
                'code' => 200
            ])->setStatusCode(200);
        }
        // $midtransCheck = new MidtransService($checkPayment);
        // $getStatus = get_object_vars((object)$midtransCheck->checkStatus());


        DB::beginTransaction();
        try {
            
            
            $order_items = $orders->order_item()->get();
            $gross_total = 0;
            $checkItem = [];

            foreach ($order_items as $key => $items) {
                $checkItem[] = (float) $items->product()->first()->price * $items->quantity;
                $gross_total = (float) $items->product->first()->price * $items->quantity;
                // dd($gross_total);
            }
            dd($gross_total);
            $payment_prep = [
                'order_id' => (int)$order_id,
                'transaction_id' => Str::random(10),
                'payment_type' => 'bank_transfer',
                'gross_amount' => $gross_total,
                'transaction_time' => Carbon::now()->toDateTimeString(),
                'transaction_status' => 'pending'
            ];
            $payment = new Payment();
            $payment->fill($payment_prep);
            // dd($payment->getAttribute('gross_amount'));

            $midtrans = new MidtransService($payment);
            // dd($payment);
            $va = $midtrans->makeVAforBCA();
            if ($va === false) {
                # code...
                DB::rollBack();
                return response()->json(['message' => 'The request could not be completed due to a conflict with the current state of the target resource'])->setStatusCode(400);
            }
            $payment->save();
            DB::commit();



            return response()->json([
                "data" => $va
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return response()->json(
                [
                    "error" => $th->getTrace(),
                    "code" => 400
                ]
            );
        }
    }

    // public function store(Request $request)
    // {
    //     //

    //     DB::beginTransaction();
    //     try {
    //         //code...
    //         // dd($request->all());
    //         $validated = Validator::make($request->except('user_auth'), [
    //             'data.attributes.order_id' => 'required|exists:orders,id',
    //             'data.attributes.payment_type' => ['required', Rule::in(['cash','online'])]
    //         ]);
    //         // dd($validated);
    //         // $validated->validate();
    //         if ($validated->fails()) {
    //             # code...
    //             return response()->json(
    //                 [
    //                     "error" => $validated->messages(),
    //                     "code" => 400
    //                 ]
    //             );
    //         }
    //         // $this->validate($request, [
    //         //     'data.attributes.order_id' => 'required|exists:orders,id',
    //         //     'data.attributes.payment_type' => 'required'
    //         // ]);
    //         $order_id = $request->input('data.attributes.order_id');

    //         $checkOrderPayment = Payment::where('order_id', $order_id)->first(); 
    //         if ($checkOrderPayment) {
    //             # code...
    //             return response()->json(['message'=>'order already exist']);
    //         }

    //         // dd($checkOrderPayment);
    //         $payment_type = $request->input('data.attributes.payment_type');
    //         $order = Order::where('id', $order_id)->first();
    //         $orderItem = $order->order_item()->get();
    //         $grossTotal = 0;
    //         foreach ($orderItem as $key => $item) {
    //             # code...
    //             $grossTotal += (float) $item->product()->first()->price;
    //         }
    //         $payment = [
    //             'order_id' => $order_id,
    //             'transaction_id' => Str::random(10),
    //             'payment_type' => $payment_type,
    //             'gross_amount' => $grossTotal,
    //             'transaction_time' => Carbon::now()->toDateTimeString(),
    //             'transaction_status' => 'Wait for payment'
    //         ];
    //         // dd(Payment::create($payment));
    //         // Payment::create($payment);
    //         // $createdPayment = Payment::create($payment);
    //         // $createdPayment->fill($payment);
    //         $createdPayment = new Payment();
    //         $createdPayment->fill($payment);
    //         // dd($createdPayment);
    //         // dd( $request->attributes->get('user_auth'));

    //         // $midtrans = new CreateSnapAndRedirectService($createdPayment);
    //         // $snapData = $midtrans->getSnapAndUrl();


    //         // dd($midtrans->getSnapAndUrl());
    //         // dd($createdPayment);
    //         // dd($midtrans->getSnapAndUrl());
    //         $orderUpdate = Order::find($order_id);
    //         $orderUpdate->status = 'success';
    //         $orderUpdate->save();
    //         DB::commit();
    //         // return response()->json([
    //         //     "message" => "Payment issued",
    //         //     "data" => $snapData,
    //         //     "status" => true,
    //         // ])->setStatusCode(200);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         // $midtrans = new CreateSnapAndRedirectService();
    //         // dd($midtrans);
    //         //throw $th;
    //         return response()->json([
    //             "error" => $th->getMessage(),
    //             "code"=> 500
    //         ])->setStatusCode(500);
    //     }
    // }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        //
    }
}
