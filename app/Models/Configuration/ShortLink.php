<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class ShortLink extends Model
{
    protected $table = 'short_links';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;
}
