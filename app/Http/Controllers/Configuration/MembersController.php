<?php

namespace App\Http\Controllers\Configuration;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Contracts\Configuration\MembersInterface;
use App\Http\Requests\Modules\Configuration\Members\MembersCreateRequest;
use App\Http\Requests\Modules\Configuration\Members\MembersUpdateRequest;

class MembersController extends Controller
{
    use ApiResponser;

    protected $members;

    public function __construct(MembersInterface $members)
    {
        $this->members = $members;
    }

    public function listPaginate(Request $request)
    {
        $data = $this->members->paginate($request);
        return $this->set_response($data,  200, 'success', ['Data list']);
    }

    public function singleData($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:members,id',
        ]);

        if ($validator->fails()) {
            return $this->set_response(null, 422, 'error', $validator->errors()->all());
        }

        $data = $this->members->show($id);

        return $this->set_response($data, 200, 'success', ['Single data']);
    }

    public function create(MembersCreateRequest $request)
    {
        try {
            $data = $this->members->store($request);

            return $this->set_response($data, 200, 'success', ['Data created successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function update(MembersUpdateRequest $request)
    {
        try {
            $data = $this->members->update($request);

            return $this->set_response($data, 200, 'success', ['Data Updated successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function filterData( Request $request ) {
        $data = $this->members->filterData($request);
        return $this->set_response($data,  200, 'success', ['filter list']);
    }
}
