<?php

namespace App\Models\Configuration;

use App\Models\Configuration\Events;
use Illuminate\Database\Eloquent\Model;
use App\Models\Configuration\EventTicketStatus;
use App\Models\Configuration\EventTicketPurchase;

class EventTicket extends Model {
    protected $table = 'event_ticket';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;

    public function events()
    {
        return $this->belongsTo(Events::class, 'event_id', 'id');
    }
    public function event_ticket_status()
    {
        return $this->belongsTo(EventTicketStatus::class, 'event_ticket_status_id', 'id');
    }

    public function event_ticket_purchase()
    {
        return $this->belongsToMany(EventTicketPurchase::class, 'event_ticket_purchase_multi_ticket',  'event_ticket_id', 'event_ticket_purchase_id');
    }

}

