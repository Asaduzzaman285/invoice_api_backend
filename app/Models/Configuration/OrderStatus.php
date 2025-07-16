<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model {
    protected $table = 'order_status';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;


}

