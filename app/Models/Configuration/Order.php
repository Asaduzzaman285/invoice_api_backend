<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;
use App\Models\Configuration\OrderDetail;
use App\Models\Configuration\OrderStatus;
use App\Models\Configuration\PaymentMethod;
use App\Models\Configuration\PaymentStatus;
use App\Models\Configuration\ShipmentStatus;

class Order extends Model {
    protected $table = 'order';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;

    public function order_detail()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }
    public function payment_status()
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id', 'id');
    }
    public function shipment_status()
    {
        return $this->belongsTo(ShipmentStatus::class, 'shipment_status_id', 'id');
    }
    public function order_status()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id', 'id');
    }
}

