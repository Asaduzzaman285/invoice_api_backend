<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model {
    protected $table = 'payment_method';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;

}

