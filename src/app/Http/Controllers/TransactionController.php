<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $transaction = DB::connection('mysql')->table('transactions')->get();
        return response()->json($transaction);
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
        //
        $this->validate($request, [
            'product_id' => 'required|integer',
            'qty' => 'required|integer',
        ]);



        $product = DB::connection('mysql')->table('products')->where('id', $request->input('product_id'))->first();

        $qty = $request->input('qty');

        if ((int)$product->qty < (int) $request->input('qty')) {
            # code...
            return response()->json([
                "message" => "Stock qty is not enough"
            ])->setStatusCode(400);
        }

        DB::connection('mysql')->table('products')->where('id', $request->input('product_id'))->update([
            "qty" => (int) $qty - (int) $request->input('qty')
        ]);

        $total = (int) $product->price * (int) $qty;

        // $product = DB::connection('mysql')->table('products')->where('id', $request->input('product_id'))->first();

        $transaction = [
            'product_id' => $request->input('product_id'),
            'total_transaksi' => $total,
        ];

        $storeID = DB::connection('mysql')->table('transactions')->insertGetId($transaction);

        $data = DB::connection('mysql')->table('transactions')->where('id', $storeID)->first();

        $response = [
            'success' => True,
            'message' => 'transaction added',
            'transaction' => $data
        ];

        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $transaction = DB::connection('mysql')->table('transactions')->where('id', $id)->first();
        return response()->json($transaction);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $this->validate($request, [
            'product_id' => 'required|integer',
            'qty' => 'required|integer',
        ]);
        $product = DB::connection('mysql')->table('products')->where('id', $request->input('product_id'))->first();

        $qty = $request->input('qty');

        $total = (int) $product->price * (int) $qty;

        $transaction = [
            'product_id' => $request->input('product_id'),
            'total_transaksi' => $total,
        ];

        DB::connection('mysql')->table('transactions')->where('id', $id)->update($transaction);

        $data = DB::connection('mysql')->table('transactions')->where('id', $id)->first();

        $response = [
            'success' => True,
            'message' => 'transaction added',
            'transaction' => $data
        ];
        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
