<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class EventTicketStatus extends Model {
    protected $table = 'event_ticket_status';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;


}

