<?php

namespace App\Repositories\Configuration;

use App\Enum\PaginationEnum;
use App\Models\Configuration\Events;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuration\EventTicketType;
use App\Contracts\Configuration\EventTicketTypeInterface;

class EventTicketTypeRepository implements EventTicketTypeInterface
{
    public function paginate($request)
    {
        [$sort_field, $sort_order] = processOrderBy('id', 'DESC', null, $request->sort['column'] ?? null,  $request->sort['order'] ?? null);

        $data = EventTicketType::when(isset($sort_field), function ($query) use ($sort_field, $sort_order) {
            return $query->orderBy($sort_field, $sort_order);
        })
        ->when(request()->filled('event_id'), function ($query) {
            $query->where('id', request('event_id'));
        })
        ->when(request()->filled('ticket_type'), function ($query) {
            $query->where('ticket_type', request('ticket_type'));
        })
        ->with('events:id,title,artist')
        ;
        $data = $data->paginate(PaginationEnum::$DEFAULT);

        $data = [
            'paginator' => getFormattedPaginatedArray($data),
            'data' => $data->items(),
        ];

        return $data;
    }

    public function show($id)
    {
        $data = EventTicketType::where('id', $id)->with('events:id,title,artist')->first();
        return $data;
    }

    public function store($request)
    {
        return EventTicketType::create([
            'ticket_type' => $request->ticket_type ?? null,
            'event_id' => $request->event_id ?? null,
            'price' => $request->price ?? null,

            'created_at' => getNow(),
            'created_by' => Auth::user()->id,
        ]);
    }

    public function update($request)
    {
        $data = EventTicketType::findOrFail($request->id);
        $data->update([
            'ticket_type' => $request->ticket_type ?? null,
            'event_id' => $request->event_id ?? null,
            'price' => $request->price ?? null,

            'updated_at' => getNow(),
            'updated_by' => Auth::user()->id,
        ]);
        return  $data;
    }

    public function filterData($request)
    {
        $event_list = Events::select('id as value', 'title as label')->distinct()->get();
        $ticket_types = EventTicketType::select('id as value', 'ticket_type as label')->distinct()->get();
        $data = [
            'event_list' => $event_list,
            'ticket_types' => $ticket_types,
        ];

        return $data;
    }
}
