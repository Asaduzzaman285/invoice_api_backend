<?php

namespace App\Repositories\Configuration;

use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuration\Members;
use App\Models\Configuration\SuccessStories;
use App\Contracts\Configuration\SuccessStoriesInterface;

class SuccessStoriesRepository implements SuccessStoriesInterface
{
    public function paginate($request)
    {
        [$sort_field, $sort_order] = processOrderBy('id', 'DESC', null, $request->sort['column'] ?? null,  $request->sort['order'] ?? null);

        $data = SuccessStories::when(isset($sort_field), function ($query) use ($sort_field, $sort_order) {
            return $query->orderBy($sort_field, $sort_order);
        })
        ->when(request()->filled('success_stories_id'), function ($query) {
            $query->where('id', request('success_stories_id'));
        })
        ->with('creator:id,name','modifier:id,name')
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
        $data = SuccessStories::findOrFail($id);
        return $data;
    }

    public function store($request)
    {
        return SuccessStories::create([
            'headline' => $request->headline ?? null,
            // 'subheading' => $request->subheading ?? null,
            'file_path' => $request->file_path ?? null,
            'file_name' => $request->file_name ?? null,
            'details' => $request->details ?? null,
            // 'member_id' => $request->member_id ?? null,
            'posting_time' => $request->posting_time ?? null,

            'created_at' => getNow(),
            'created_by' => Auth::user()->id,
        ]);
    }

    public function update($request)
    {
        $data = SuccessStories::findOrFail($request->id);
        $data->update([
            'headline' => $request->headline ?? null,
            // 'subheading' => $request->subheading ?? null,
            'file_path' => $request->file_path ?? null,
            'file_name' => $request->file_name ?? null,
            'details' => $request->details ?? null,
            // 'member_id' => $request->member_id ?? null,
            'posting_time' => $request->posting_time ?? null,

            'updated_at' => getNow(),
            'updated_by' => Auth::user()->id,
        ]);
        return  $data;
    }

    public function filterData($request)
    {
        $headline = SuccessStories::select('id as value', 'headline as label')->distinct()->get();
        $data = [
            'headline' => $headline,
        ];
        return $data;
    }
}
