<?php

namespace App\Http\Controllers\Configuration;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Contracts\Configuration\HomeMainSliderInterface;
use App\Http\Requests\Modules\Configuration\HomeMainSlider\HomeMainSliderCreateRequest;
use App\Http\Requests\Modules\Configuration\HomeMainSlider\HomeMainSliderUpdateRequest;

class HomeMainSliderController extends Controller
{
    use ApiResponser;

    protected $home_main_slider;

    public function __construct(HomeMainSliderInterface $home_main_slider)
    {
        $this->home_main_slider = $home_main_slider;
    }

    public function listPaginate(Request $request)
    {
        $data = $this->home_main_slider->paginate($request);
        return $this->set_response($data,  200, 'success', ['Data list']);
    }

    public function singleData($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:home_main_slider,id',
        ]);

        if ($validator->fails()) {
            return $this->set_response(null, 422, 'error', $validator->errors()->all());
        }

        $data = $this->home_main_slider->show($id);

        return $this->set_response($data, 200, 'success', ['Single data']);
    }

    public function create(HomeMainSliderCreateRequest $request)
    {
        try {
            $data = $this->home_main_slider->store($request);

            return $this->set_response($data, 200, 'success', ['Data created successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function update(HomeMainSliderUpdateRequest $request)
    {
        try {
            $data = $this->home_main_slider->update($request);

            return $this->set_response($data, 200, 'success', ['Data Updated successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function filterData( Request $request ) {
        $data = $this->home_main_slider->filterData($request);
        return $this->set_response($data,  200, 'success', ['filter list']);
    }
}
