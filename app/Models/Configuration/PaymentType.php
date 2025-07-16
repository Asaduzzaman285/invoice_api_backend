<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model {
    protected $table = 'payment_type';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;
}

