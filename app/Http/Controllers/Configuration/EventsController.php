<?php

namespace App\Http\Controllers\Configuration;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Contracts\Configuration\EventsInterface;
use App\Http\Requests\Modules\Configuration\Events\EventsCreateRequest;
use App\Http\Requests\Modules\Configuration\Events\EventsUpdateRequest;

class EventsController extends Controller
{
    use ApiResponser;

    protected $events;

    public function __construct(EventsInterface $events)
    {
        $this->events = $events;
    }

    public function listPaginate(Request $request)
    {
        $data = $this->events->paginate($request);
        return $this->set_response($data,  200, 'success', ['Data list']);
    }

    public function singleData($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:events,id',
        ]);

        if ($validator->fails()) {
            return $this->set_response(null, 422, 'error', $validator->errors()->all());
        }

        $data = $this->events->show($id);

        return $this->set_response($data, 200, 'success', ['Single data']);
    }

    public function create(EventsCreateRequest $request)
    {
        try {
            $data = $this->events->store($request);

            return $this->set_response($data, 200, 'success', ['Data created successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function update(EventsUpdateRequest $request)
    {
        try {
            $data = $this->events->update($request);

            return $this->set_response($data, 200, 'success', ['Data Updated successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function filterData( Request $request ) {
        $data = $this->events->filterData($request);
        return $this->set_response($data,  200, 'success', ['filter list']);
    }
}
