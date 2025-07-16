<?php

namespace App\Http\Controllers\Configuration;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Contracts\Configuration\SuccessStoriesInterface;
use App\Http\Requests\Modules\Configuration\SuccessStories\SuccessStoriesCreateRequest;
use App\Http\Requests\Modules\Configuration\SuccessStories\SuccessStoriesUpdateRequest;

class SuccessStoriesController extends Controller
{
    use ApiResponser;

    protected $success_stories;

    public function __construct(SuccessStoriesInterface $success_stories)
    {
        $this->success_stories = $success_stories;
    }

    public function listPaginate(Request $request)
    {
        $data = $this->success_stories->paginate($request);
        return $this->set_response($data,  200, 'success', ['Data list']);
    }

    public function singleData($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:success_stories,id',
        ]);

        if ($validator->fails()) {
            return $this->set_response(null, 422, 'error', $validator->errors()->all());
        }

        $data = $this->success_stories->show($id);

        return $this->set_response($data, 200, 'success', ['Single data']);
    }

    public function create(SuccessStoriesCreateRequest $request)
    {
        try {
            $data = $this->success_stories->store($request);

            return $this->set_response($data, 200, 'success', ['Data created successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function update(SuccessStoriesUpdateRequest $request)
    {
        try {
            $data = $this->success_stories->update($request);

            return $this->set_response($data, 200, 'success', ['Data Updated successfully']);
        } catch (\Exception $e) {
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            return $this->set_response(null,  422, 'error', ['Something went wrong. Please try again later!']);
        }
    }

    public function filterData( Request $request ) {
        $data = $this->success_stories->filterData($request);
        return $this->set_response($data,  200, 'success', ['filter list']);
    }
}
