<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mysql';
    protected $table = 'products';

    public function order_item(){
        return $this->hasMany('App\Models\OrderItem');
    }
}
