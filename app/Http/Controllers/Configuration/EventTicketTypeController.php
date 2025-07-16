<?php

namespace App\Http\Controllers\Configuration;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Contracts\Configuration\EventTicketTypeInterface;
use App\Http\Requests\Modules\Configuration\EventTicketType\EventTicketTypeCreateRequest;
use App\Http\Requests\Modules\Configuration\EventTicketType\EventTicketTypeUpdateRequest;

class EventTicketTypeController extends Controller
{
    use ApiResponser;

    protected $event_ticket_type;

    public function __construct(EventTicketTypeInterface $event_ticket_type)
    {
        $this->event_ticket_type = $event_ticket_type;
    }

    public function listPaginate(Request $request)
    {
        $data = $this->event_ticket_type->paginate($request);
        return $this->set_response($data,  200, 'success', ['Data list']);
    }

    public function singleData($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:event_ticket_type,id',
        ]);

        if ($validator->fails()) {
            return $this->set_response(null, 422, 'error', $validator->errors()->all());
        }

        $data = $this->event_ticket_type->show($id);

        return $this->set_response($data, 200, 'success', ['Single data']);
    }

    public function create(EventTicketTypeCreateRequest $request)
    {
        try {
            $data = $this->event_ticket_type->store($request);

            return $this->set_response($data, 200, 'success', ['Data created successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function update(EventTicketTypeUpdateRequest $request)
    {
        try {
            $data = $this->event_ticket_type->update($request);

            return $this->set_response($data, 200, 'success', ['Data Updated successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function filterData( Request $request ) {
        $data = $this->event_ticket_type->filterData($request);
        return $this->set_response($data,  200, 'success', ['filter list']);
    }
}
