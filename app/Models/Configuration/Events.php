<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;
use App\Models\Configuration\EventTicket;
use App\Models\Configuration\EventTicketType;
use App\Models\Configuration\EventTicketPurchase;

class Events extends Model {
    protected $table = 'events';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;

    public function event_ticket_purchase()
    {
        return $this->hasMany(EventTicketPurchase::class, 'event_id', 'id');
    }
    public function event_ticket()
    {
        return $this->hasMany(EventTicket::class, 'event_id', 'id');
    }
    public function event_ticket_type()
    {
        return $this->hasMany(EventTicketType::class, 'event_id', 'id');
    }

}

