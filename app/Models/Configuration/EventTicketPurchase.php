<?php

namespace App\Models\Configuration;

use App\Models\Configuration\Events;
use Illuminate\Database\Eloquent\Model;
use App\Models\Configuration\EventTicket;
use App\Models\Configuration\PaymentType;

class EventTicketPurchase extends Model {
    protected $table = 'event_ticket_purchase';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;


    public function events()
    {
        return $this->belongsTo(Events::class, 'event_id', 'id');
    }
    public function payment_type()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id', 'id');
    }

    public function event_ticket()
    {
        return $this->belongsToMany(EventTicket::class, 'event_ticket_purchase_multi_ticket',  'event_ticket_purchase_id', 'event_ticket_id');
    }
}

