<?php

namespace App\Repositories\Configuration;

use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuration\Events;
use App\Contracts\Configuration\EventsInterface;

class EventsRepository implements EventsInterface
{
    public function paginate($request)
    {
        [$sort_field, $sort_order] = processOrderBy('id', 'DESC', null, $request->sort['column'] ?? null,  $request->sort['order'] ?? null);

        $data = Events::when(isset($sort_field), function ($query) use ($sort_field, $sort_order) {
            return $query->orderBy($sort_field, $sort_order);
        })
        ->when(request()->filled('event_id'), function ($query) {
            $query->where('id', request('event_id'));
        })
        ->when(request()->filled('title'), function ($query) {
            $query->where('title', request('title'));
        })
        ->when(request()->filled('artist'), function ($query) {
            $query->where('artist', request('artist'));
        })
        ->when(request()->filled('start_date'), function ($query) {
            $query->where('date', '>=',request('start_date'));
        })
        ->when(request()->filled('end_date'), function ($query) {
            $query->where('date', '<=',request('end_date'));
        })
        ->when(request()->filled('location'), function ($query) {
            $query->where('location', request('location'));
        })
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
        $data = Events::where('id', $id)->first();
        return $data;
    }

    public function store($request)
    {
        return Events::create([
            'title' => $request->title ?? null,
            'artist' => $request->artist ?? null,
            'description' => $request->description ?? null,
            'date' => $request->date ?? null,
            'location' => $request->location ?? null,
            'file_name' => $request->file_name ?? null,
            'file_path' => $request->file_path ?? null,

            'created_at' => getNow(),
            'created_by' => Auth::user()->id,
        ]);
    }

    public function update($request)
    {
        $data = Events::findOrFail($request->id);
        $data->update([
            'title' => $request->title ?? null,
            'artist' => $request->artist ?? null,
            'description' => $request->description ?? null,
            'date' => $request->date ?? null,
            'location' => $request->location ?? null,
            'file_name' => $request->file_name ?? null,
            'file_path' => $request->file_path ?? null,

            'updated_at' => getNow(),
            'updated_by' => Auth::user()->id,
        ]);
        return  $data;
    }

    public function filterData($request)
    {
        $title_list = Events::select('id as value', 'title as label')->distinct()->get();
        $artist_list = Events::select('id as value', 'artist as label')->distinct()->get();
        $location_list = Events::select('id as value', 'location as label')->distinct()->get();
        $data = [
            'title_list' => $title_list,
            'artist_list' => $artist_list,
            'location_list' => $location_list,
        ];

        return $data;
    }
}
