<?php

namespace App\Repositories\Configuration;

use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuration\Members;
use App\Models\Configuration\Product;
use App\Contracts\Configuration\ProductInterface;

class ProductRepository implements ProductInterface
{
    public function paginate($request)
    {
        [$sort_field, $sort_order] = processOrderBy('id', 'DESC', null, $request->sort['column'] ?? null,  $request->sort['order'] ?? null);

        $data = Product::when(isset($sort_field), function ($query) use ($sort_field, $sort_order) {
            return $query->orderBy($sort_field, $sort_order);
        })
        ->when(request()->filled('product_id'), function ($query) {
            $query->where('id', request('product_id'));
        })
        ->when(request()->filled('member_id'), function ($query) {
            $query->where('member_id', request('member_id'));
        })
        ->with('member:id,name')
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
        $data = Product::where('id', $id)->with('member:id,name')->first();
        return $data;
    }

    public function store($request)
    {
        return Product::create([
            'name' => $request->name ?? null,
            'price' => $request->price ?? null,
            'description' => $request->description ?? null,
            'member_id' => $request->member_id ?? null,
            'file_name' => $request->file_name ?? null,
            'file_path' => $request->file_path ?? null,

            'created_at' => getNow(),
            'created_by' => Auth::user()->id,
        ]);
    }

    public function update($request)
    {
        $data = Product::findOrFail($request->id);
        $data->update([
            'name' => $request->name ?? null,
            'price' => $request->price ?? null,
            'description' => $request->description ?? null,
            'member_id' => $request->member_id ?? null,
            'file_name' => $request->file_name ?? null,
            'file_path' => $request->file_path ?? null,

            'updated_at' => getNow(),
            'updated_by' => Auth::user()->id,
        ]);
        return  $data;
    }

    public function filterData($request)
    {
        $name_list = Product::select('id as value', 'name as label')->distinct()->get();
        // Get distinct authors (members) from products
        $author_list = Members::whereIn('id', Product::pluck('member_id')->unique())
                        ->select('id as value', 'name as label')
                        ->orderBy('name')
                        ->get();
        $data = [
            'name_list' => $name_list,
            'author_list' => $author_list,
        ];

        return $data;
    }
}
