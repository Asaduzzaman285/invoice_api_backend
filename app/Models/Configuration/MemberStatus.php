<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class MemberStatus extends Model {
    protected $table = 'member_status';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;

}

