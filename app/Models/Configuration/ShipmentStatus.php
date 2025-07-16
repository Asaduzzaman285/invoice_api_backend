<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class ShipmentStatus extends Model {
    protected $table = 'shipment_status';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;

}

