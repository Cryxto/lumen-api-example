<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    //
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'price', 'qty'
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

}
