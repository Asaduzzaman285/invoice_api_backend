<?php

namespace App\Repositories\Configuration;

use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;
use App\Models\CorporateService;
use App\Contracts\Configuration\CorporateServiceInterface;
use Illuminate\Http\Request;

class CorporateServiceRepository implements CorporateServiceInterface
{
    public function paginate($request)
    {
        [$sort_field, $sort_order] = processOrderBy('id', 'ASC', null, $request->sort['column'] ?? null, $request->sort['order'] ?? null);

        $data = CorporateService::when(isset($sort_field), function ($query) use ($sort_field, $sort_order) {
            return $query->orderBy($sort_field, $sort_order);
        });

        $data = $data->paginate(PaginationEnum::$DEFAULT);

        return [
            'paginator' => getFormattedPaginatedArray($data),
            'data' => $data->items(),
        ];
    }

    public function show($id)
    {
        return CorporateService::findOrFail($id);
    }

    public function store($request)
    {
        return CorporateService::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_at' => getNow(),
            // 'created_by' => Auth::user()->id ?? null,
        ]);
    }

    public function update($request)
    {
        $service = CorporateService::findOrFail($request->id);
        $service->update([
            'title' => $request->title,
            'description' => $request->description,
            'updated_at' => getNow(),
            // 'updated_by' => Auth::user()->id,
        ]);
        return $service;
    }

    public function delete($id)
    {
        return CorporateService::destroy($id);
    }

    public function filterData($request)
    {
        return []; // Add custom filtering logic if needed
    }
}