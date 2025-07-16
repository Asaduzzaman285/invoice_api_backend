<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {
    protected $table = 'product';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;

    public function member() {
        return $this->belongsTo( Members::class, 'member_id', 'id' );
    }

    public function creator() {
        return $this->belongsTo( User::class,  'created_by', 'id' );
    }

    public function modifier() {
        return $this->belongsTo( User::class,  'updated_by', 'id' );

    }
}

