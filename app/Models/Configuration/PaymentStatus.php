<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class PaymentStatus extends Model {
    protected $table = 'payment_status';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;

}

