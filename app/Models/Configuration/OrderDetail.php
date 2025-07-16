<?php

namespace App\Models\Configuration;

use App\Models\Configuration\Order;
use App\Models\Configuration\Product;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model {
    protected $table = 'order_detail';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;


    public function order() {
        return $this->belongsTo( Order::class, 'order_id', 'id' );
    }
    public function product() {
        return $this->belongsTo( Product::class, 'product_id', 'id' );
    }
}

