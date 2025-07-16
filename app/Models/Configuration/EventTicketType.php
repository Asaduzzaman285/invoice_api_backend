<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class EventTicketType extends Model {
    protected $table = 'event_ticket_type';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;

    public function events()
    {
        return $this->belongsTo(Events::class, 'event_id', 'id');
    }

}

