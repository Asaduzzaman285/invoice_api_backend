<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
          $id = $this->route('id');
        return [

          'client_id' => 'required|integer',
            'invoice_number' => 'required|string|max:100|unique:invoice,invoice_number,' . $id,
            'kam' => 'nullable|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'client_address' => 'nullable|string|max:500',
            'company_id' => 'nullable|integer',
            'billing_date' => 'nullable|date',
            'prepared_by' => 'nullable|string|max:255',
            'received_by' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'pmnt_rcv_acc_id' => 'nullable|integer',
            'pmnt_rcv_bank' => 'nullable|string|max:255',
            'pmnt_rcv_acc' => 'nullable|string|max:255',
            'pmnt_rcv_branch' => 'nullable|string|max:255',
            'pmnt_rcv_rn' => 'nullable|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'invoice_details' => 'required|array|min:1',
            'invoice_details.*.description' => 'required|string',
            'invoice_details.*.sms_qty' => 'required|numeric|min:0',
            'invoice_details.*.unit_price' => 'required|numeric|min:0',
            'invoice_details.*.total' => 'required|numeric|min:0',

        ];
    }
}
