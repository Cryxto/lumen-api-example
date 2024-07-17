<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

function payment_type () { 
    return [
        "cash"=>"cash",
        "online"=>"online",
        "bank_transfer"=>"bank_transfer"
    ];
}
class Payment extends Model
{
    protected $connection = 'mysql';
    protected $table = 'payments';
    protected $fillable = [
        'id',
        'order_id',
        'transaction_id',
        'payment_type',
        'gross_amount',
        'transaction_time',
        'transaction_status'
    ];

    public function order(){
        return $this->belongsTo('App\Models\Order');
    }

    
}
