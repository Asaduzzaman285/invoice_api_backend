<?php

namespace App\Repositories\Configuration;

use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuration\HomeAds;
use App\Contracts\Configuration\HomeAdsInterface;

class HomeAdsRepository implements HomeAdsInterface
{
    public function paginate($request)
    {
        [$sort_field, $sort_order] = processOrderBy('id', 'DESC', null, $request->sort['column'] ?? null,  $request->sort['order'] ?? null);

        $data = HomeAds::when(isset($sort_field), function ($query) use ($sort_field, $sort_order) {
            return $query->orderBy($sort_field, $sort_order);
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
        $data = HomeAds::where('id', $id)->first();
        return $data;
    }

    public function store($request)
    {
        return HomeAds::create([
            'file_name' => $request->file_name ?? null,
            'file_path' => $request->file_path ?? null,

            'created_at' => getNow(),
            'created_by' => Auth::user()->id,
        ]);
    }

    public function update($request)
    {
        $data = HomeAds::findOrFail($request->id);
        $data->update([
            'file_name' => $request->file_name ?? null,
            'file_path' => $request->file_path ?? null,

            'updated_at' => getNow(),
            'updated_by' => Auth::user()->id,
        ]);
        return  $data;
    }

    public function filterData($request)
    {
        $data = [
        ];

        return $data;
    }
}
