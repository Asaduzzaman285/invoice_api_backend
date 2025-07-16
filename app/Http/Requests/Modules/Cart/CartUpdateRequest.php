<?php

namespace App\Http\Requests\Modules\Cart;

use App\Traits\ApiResponser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class CartUpdateRequest extends FormRequest
{
    use ApiResponser;

    public function rules()
    {
        $rules = [
            'id' => 'required|integer|exists:order,id',

            'paid_amount' => 'required|numeric',
            'due' => 'required|numeric',
            'payment_method_id' => 'required|integer|exists:payment_method,id',
            'shipment_status_id' => 'nullable|integer|exists:shipment_status,id',
            'shipment_date' => 'nullable|date',
            'payment_status_id' => 'nullable|integer|exists:payment_status,id',
            'payment_date' => 'nullable|date',
            'order_status_id' => 'nullable|integer|exists:order_status,id',
        ];

        return $rules;
    }

    public function messages()
    {
        $messages = [

        ];

        return $messages;
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->set_response(null, 422, 'error', array_slice($validator->errors()->all(), 0, 2), formatErrors($validator)));
    }
}
