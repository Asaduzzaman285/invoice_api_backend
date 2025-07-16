<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class HomeAds extends Model {
    protected $table = 'home_ads';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;

    public function creator() {
        return $this->belongsTo( User::class,  'created_by', 'id' );
    }

    public function modifier() {
        return $this->belongsTo( User::class,  'updated_by', 'id' );

    }
}

