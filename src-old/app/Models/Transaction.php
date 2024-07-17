<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    //
    protected $connection = 'mysql';
    protected $fillable = [
        'product_id', 'total_transaksi'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
