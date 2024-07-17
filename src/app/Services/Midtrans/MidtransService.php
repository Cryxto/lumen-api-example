<?php

namespace App\Services\Midtrans;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Midtrans\CoreApi;
use Midtrans\Notification;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService extends Midtrans
{
  private $payment;
  private $user;
  private $order;
  private $orderItems;
  private $params;


  public function __construct(Payment $payment)
  {
    // dd('dasasdasdasd');
    parent::__construct();

    $this->payment = $payment;
    $this->user = request()->attributes->get('user_auth');
    $this->setOrder();
    $this->setOrderItems();
    $this->setParams();
    // if ($this->payment) {
    //   dd('Payment in Service: ', $this->payment);
    // } else {
    //   dd('Payment is null in Service');
    // }
    // dd($this->payment);

  }


  private function setOrder()
  {
    $this->order = $this->payment->order()->first();
  }

  private function setOrderItems()
  {
    $this->orderItems = $this->payment->order->order_item()->with('product')->get();
  }

  private function setParams()
  {
    $gross_amount = $this->payment->getAttribute('gross_amount');
    $order_id = $this->payment->getAttribute('transaction_id');
    $this->params = [
      "payment_type" => "bank_transfer",
      "transaction_details" => [
        "order_id" => $order_id,
        // "order_id" => $order_id,
        "gross_amount" => $gross_amount
      ],
    ];
  }

  public function checkParams() {
    return $this->payment->getAttribute('gross_amount');
  }

  public function makeVAforBCA() {
    $data = $this->params;
    $data["bank_transfer"] = [
      "bank"=>"bca"
    ];
    try {
      $createdVA = CoreApi::charge($data);
      // if (strpos($createdVA, 'status code: 406') !== false){
      //   return false;
      // }
      return $createdVA;
      //code...
    } catch (\Throwable $th) {
      //throw $th;
      if (strpos($th->getMessage(), 'status code: 406') !== false){
        // return false;
        return false;
      }
    }
  }

  public function checkStatus() {
    // return Transaction::status($this->payment->transaction_id);
    // $checkStatusPayment = new Notification();
    // Fetch the transaction status from Midtrans
    $checkStatusPayment = Transaction::status($this->payment->transaction_id);

    // Encode the transaction status to JSON
    // $jsonTransactionStatus = json_encode($checkStatusPayment);

    // Simulate php://input

    // Create a new Notification instance
    // $checkStatusPayment = new Notification($jsonTransactionStatus);

    // return json_decode($jsonTransactionStatus,true);

    return $checkStatusPayment;
  }



  public function getOrderAndItsItems()
  {
    return [
      "order" => $this->order,
      "orderItems" => $this->orderItems
    ];
  }

  // private function setOrder() {
  //   $this->order = $this->payment->order()->first();
  // }

  // private function setOrderItems() {
  //   $orderItems = $this->order->order_item()->with('product')->get();

  //   $orderItems->each(function ($orderItem) {
  //     $orderItem->makeHidden(['created_at', 'updated_at', 'order_id', 'product_id', 'product']);
  //     $orderItem->product->makeHidden(['created_at', 'updated_at']);
  //     $orderItem->setAttribute('product_id', $orderItem->product->id);
  //     $orderItem->setAttribute('price', $orderItem->product->price);
  //     $orderItem->setAttribute('name', $orderItem->product->name);
  //   });
  // }

  // private function setParams() {
  //   $this->params = [
  //     'transaction_details' => [
  //       'order_id' => $this->payment->transaction_id,
  //       'gross_amount' => $this->payment->gross_amount,
  //     ],
  //     'item_details' => $this->orderItems,
  //     'customer_details' => [
  //       'first_name' => $this->user->username,
  //       'email' => 'foo@foo.com',
  //       'phone' => '086234567890',
  //     ]
  //   ];
  // }

  // public function setVirtualAccountBCA() {

  // }

  // public function getSnapAndUrl()
  // {
  //   $order = $this->payment->order()->first();
  //   // $orderItems = $order->order_item()->with('product')->get(
  //   //   // [
  //   //   //   'product.id',
  //   //   //   'product.price',
  //   //   //   'payments.qty as quantity',
  //   //   //   'product.name'
  //   //   // ]
  //   // );

  //   // $orderItems = DB::table('order_items')
  //   // ->join('products', 'order_items.product_id', '=', 'products.id')
  //   // ->where('order_items.order_id', $order->id)
  //   // ->select([
  //   //     'products.id',
  //   //     'products.price',
  //   //     'order_items.quantity',
  //   //     'products.name'
  //   // ])
  //   // ->get();
  //   $orderItems = $order->order_item()->with('product')->get();

  //   $orderItems->each(function ($orderItem) {
  //     $orderItem->makeHidden(['created_at', 'updated_at', 'order_id', 'product_id', 'product']);
  //     $orderItem->product->makeHidden(['created_at', 'updated_at']);
  //     $orderItem->setAttribute('product_id', $orderItem->product->id);
  //     $orderItem->setAttribute('price', $orderItem->product->price);
  //     $orderItem->setAttribute('name', $orderItem->product->name);
  //   });
  //   // dd($orderItems->toJson());
  //   // $user = Auth::user();
  //   // dd($user);
  //   $params = [
  //     'transaction_details' => [
  //       'order_id' => $this->payment->transaction_id,
  //       'gross_amount' => $this->payment->gross_amount,
  //     ],
  //     'item_details' => $orderItems,
  //     'customer_details' => [
  //       'first_name' => $this->user->username,
  //       'email' => 'foo@foo.com',
  //       'phone' => '086234567890',
  //     ]
  //   ];
  //   $snap = Snap::createTransaction($params);

  //   $snapToken = $snap->token;
  //   // if (env('APP_ENV')==='local'||env('APP_ENV')==='development') {
  //   //   # code...
  //   //   $callBack = 
  //   // }
  //   $callBack = $snap->redirect_url;
  //   // $callBack = Snap::getSnapUrl($params);

  //   return ["snapToken" => $snapToken, "callBack" => $callBack];
  //   // return $this->payment;
  //   // return $order;
  // }
}
