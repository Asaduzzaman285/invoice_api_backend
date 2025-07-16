<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;
use App\Models\Configuration\MemberStatus;

class Members extends Model
{
    protected $table = 'members';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;

    public function member_status() {
        return $this->belongsTo( MemberStatus::class,  'member_status_id', 'id' );

    }
}


