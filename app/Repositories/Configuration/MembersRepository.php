<?php

namespace App\Repositories\Configuration;

use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuration\Members;
use App\Models\Configuration\MemberStatus;
use App\Contracts\Configuration\MembersInterface;

class MembersRepository implements MembersInterface
{
    public function paginate($request)
    {
        [$sort_field, $sort_order] = processOrderBy('id', 'DESC', null, $request->sort['column'] ?? null,  $request->sort['order'] ?? null);

        $data = Members::when(isset($sort_field), function ($query) use ($sort_field, $sort_order) {
            return $query->orderBy($sort_field, $sort_order);
        })
        ->when(request()->filled('member_id'), function ($query) {
            $query->where('id', request('member_id'));
        })
        ->when(request()->filled('member_status_id'), function ($query) {
            $query->where('member_status_id', request('member_status_id'));
        })
        ->when(request()->filled('is_featured'), function ($query) {
            $query->where('is_featured', request('is_featured'));
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
        $data = Members::findOrFail($id);
        return $data;
    }

    public function store($request)
    {
        return Members::create([
            'name' => $request->name ?? null,
            'position' => $request->position ?? null,
            'bio' => $request->bio ?? null,
            'youtube_url' => $request->youtube_url ?? null,
            'file_name' => $request->file_name ?? null,
            'file_path' => $request->file_path ?? null,
            'member_status_id' => $request->member_status_id ?? 2,  // 2 approved
            'is_featured' => $request->is_featured ?? 0,  // 0 = not featured,  1 = featured

            'created_at' => getNow(),
            'created_by' => Auth::user()->id,
        ]);
    }

    public function update($request)
    {
        $data = Members::findOrFail($request->id);
        $data->update([
            'name' => $request->name ?? null,
            'position' => $request->position ?? null,
            'bio' => $request->bio ?? null,
            'youtube_url' => $request->youtube_url ?? null,
            'file_name' => $request->file_name ?? null,
            'file_path' => $request->file_path ?? null,
            'member_status_id' => $request->member_status_id ?? 2,  // 2 approved
            'is_featured' => $request->is_featured ?? 0,  // 0 = not featured,  1 = featured

            'updated_at' => getNow(),
            'updated_by' => Auth::user()->id,
        ]);
        return  $data;
    }

    public function filterData($request)
    {
        $name_list = Members::select('id as value', 'name as label')->distinct()->get();
        $member_status_list = MemberStatus::select('id as value', 'status as label')->distinct()->get();
        $position_list = Members::select('position as value', 'position as label')->whereNotNull('position')->distinct()->get();
        $data = [
            'name_list' => $name_list,
            'member_status_list' => $member_status_list,
            'position_list' => $position_list,
        ];

        return $data;
    }
}
