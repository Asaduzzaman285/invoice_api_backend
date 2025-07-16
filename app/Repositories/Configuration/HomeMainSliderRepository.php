<?php

namespace App\Repositories\Configuration;

use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuration\HomeMainSlider;
use App\Contracts\Configuration\HomeMainSliderInterface;

class HomeMainSliderRepository implements HomeMainSliderInterface
{
    public function paginate($request)
    {
        [$sort_field, $sort_order] = processOrderBy('id', 'DESC', null, $request->sort['column'] ?? null,  $request->sort['order'] ?? null);

        $data = HomeMainSlider::when(isset($sort_field), function ($query) use ($sort_field, $sort_order) {
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
        $data = HomeMainSlider::where('id', $id)->first();
        return $data;
    }

public function store($request)
{
    return HomeMainSlider::create([
        'file_name' => $request->file_name ?? null,
        'file_path' => $request->file_path ?? null,
        'text' => $request->text ?? null,
        'description' => $request->description ?? null,
        'created_at' => getNow(),
        'created_by' => Auth::user()->id,
    ]);
}

public function update($request)
{
    $data = HomeMainSlider::findOrFail($request->id);
    $data->update([
        'file_name' => $request->file_name ?? null,
        'file_path' => $request->file_path ?? null,
        'text' => $request->text ?? null,
        'description' => $request->description ?? null,
        'updated_at' => getNow(),
        'updated_by' => Auth::user()->id,
    ]);
    return $data;
}


    public function filterData($request)
    {
        $data = [
        ];

        return $data;
    }
}
