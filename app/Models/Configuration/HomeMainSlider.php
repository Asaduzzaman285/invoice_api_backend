<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class HomeMainSlider extends Model {
    protected $table = 'home_main_slider';
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

