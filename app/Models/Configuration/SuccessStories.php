<?php

namespace App\Models\Configuration;

use App\Models\User;
use App\Models\Configuration\Members;
use Illuminate\Database\Eloquent\Model;

class SuccessStories extends Model
{
    protected $table = 'success_stories';
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


