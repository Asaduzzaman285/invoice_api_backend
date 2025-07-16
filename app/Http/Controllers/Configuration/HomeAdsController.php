<?php

namespace App\Http\Controllers\Configuration;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Contracts\Configuration\HomeAdsInterface;
use App\Http\Requests\Modules\Configuration\HomeAds\HomeAdsCreateRequest;
use App\Http\Requests\Modules\Configuration\HomeAds\HomeAdsUpdateRequest;

class HomeAdsController extends Controller
{
    use ApiResponser;

    protected $home_ads;

    public function __construct(HomeAdsInterface $home_ads)
    {
        $this->home_ads = $home_ads;
    }

    public function listPaginate(Request $request)
    {
        $data = $this->home_ads->paginate($request);
        return $this->set_response($data,  200, 'success', ['Data list']);
    }

    public function singleData($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:home_ads,id',
        ]);

        if ($validator->fails()) {
            return $this->set_response(null, 422, 'error', $validator->errors()->all());
        }

        $data = $this->home_ads->show($id);

        return $this->set_response($data, 200, 'success', ['Single data']);
    }

    public function create(HomeAdsCreateRequest $request)
    {
        try {
            $data = $this->home_ads->store($request);

            return $this->set_response($data, 200, 'success', ['Data created successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function update(HomeAdsUpdateRequest $request)
    {
        try {
            $data = $this->home_ads->update($request);

            return $this->set_response($data, 200, 'success', ['Data Updated successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function filterData( Request $request ) {
        $data = $this->home_ads->filterData($request);
        return $this->set_response($data,  200, 'success', ['filter list']);
    }
}
