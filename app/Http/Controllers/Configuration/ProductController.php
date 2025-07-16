<?php

namespace App\Http\Controllers\Configuration;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Contracts\Configuration\ProductInterface;
use App\Http\Requests\Modules\Configuration\Product\ProductCreateRequest;
use App\Http\Requests\Modules\Configuration\Product\ProductUpdateRequest;

class ProductController extends Controller
{
    use ApiResponser;

    protected $products;

    public function __construct(ProductInterface $products)
    {
        $this->products = $products;
    }

    public function listPaginate(Request $request)
    {
        $data = $this->products->paginate($request);
        return $this->set_response($data,  200, 'success', ['Data list']);
    }

    public function singleData($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:product,id',
        ]);

        if ($validator->fails()) {
            return $this->set_response(null, 422, 'error', $validator->errors()->all());
        }

        $data = $this->products->show($id);

        return $this->set_response($data, 200, 'success', ['Single data']);
    }

    public function create(ProductCreateRequest $request)
    {
        try {
            $data = $this->products->store($request);

            return $this->set_response($data, 200, 'success', ['Data created successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function update(ProductUpdateRequest $request)
    {
        try {
            $data = $this->products->update($request);

            return $this->set_response($data, 200, 'success', ['Data Updated successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function filterData( Request $request ) {
        $data = $this->products->filterData($request);
        return $this->set_response($data,  200, 'success', ['filter list']);
    }
}
