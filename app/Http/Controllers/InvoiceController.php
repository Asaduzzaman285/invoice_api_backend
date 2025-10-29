<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;

class InvoiceController extends Controller
{
    public function getSmsQuantity(Request $request)
    {
        try {
            $validated = $request->validate([
                'client_id' => 'required|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date',
                'description' => 'required|string'
            ]);

            // Build full URL safely
            $baseUrl = rtrim(env('SENDER_API_URL', 'http://localhost:82/api'), '/');
            $senderUrl = "{$baseUrl}/calculate-sms-quantity";
            $apiKey = env('SENDER_API_KEY', 'asad1947!52!71!24');

            Log::info('Calculating SMS quantity via Sender', [
                'url' => $senderUrl,
                'client_id' => $request->client_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'description' => $request->description
            ]);

            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post($senderUrl, $request->all());

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'success') {
                    return response()->json([
                        'status' => 'success',
                        'sms_quantity' => $data['sms_quantity'],
                        'client_id' => $data['client_id'],
                        'start_time' => $data['start_time'],
                        'end_time' => $data['end_time'],
                        'description' => $data['description']
                    ]);
                }
            }

            Log::warning('SMS quantity calculation failed, returning 0 as fallback');
            return response()->json([
                'status' => 'success',
                'sms_quantity' => 0,
                'client_id' => $request->client_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'description' => $request->description,
                'note' => 'Using fallback value'
            ]);

        } catch (\Exception $e) {
            Log::error('SMS Quantity Calculation Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'success',
                'sms_quantity' => 0,
                'client_id' => $request->client_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'description' => $request->description,
                'note' => 'Error occurred, using fallback'
            ]);
        }
    }

    public function getsupportdata(Request $request)
    {
        try {
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');

            $baseUrl = rtrim(env('SENDER_API_URL', 'http://localhost:82/api'), '/');
            $senderUrl = "{$baseUrl}/support-data";
            $apiKey = env('SENDER_API_KEY', 'asad1947!52!71!24');

            $queryParams = [];
            if ($startTime) $queryParams['start_time'] = $startTime;
            if ($endTime) $queryParams['end_time'] = $endTime;

            Log::info('Fetching support data via GET', [
                'url' => $senderUrl,
                'params' => $queryParams
            ]);

            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->get($senderUrl, $queryParams);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'success') {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Support data fetched successfully',
                        'data' => $data['data'],
                        'count' => $data['count'] ?? 0,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch data'
            ], $response->status() ?: 500);

        } catch (\Exception $e) {
            Log::error('Support Data Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Service unavailable'], 503);
        }
    }

    /**
     * Get all invoices with details with optional filtering
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('invoice as i')
                ->leftJoin('bank_account as b', 'b.id', '=', 'i.pmnt_rcv_acc_id')
                ->select(
                    'i.*',
                    'b.account_name as bank_account_name',
                    'b.branch_name as bank_branch',
                    'b.routing_number as bank_routing_number',
                    'b.account_number as bank_account_number'
                );

            // Apply filters if provided
            if ($request->has('invoice_number') && !empty($request->invoice_number)) {
                $query->where('i.invoice_number', 'like', '%' . $request->invoice_number . '%');
            }

            if ($request->has('client_name') && !empty($request->client_name)) {
                $query->where('i.client_name', 'like', '%' . $request->client_name . '%');
            }

            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->where('i.billing_date', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->where('i.billing_date', '<=', $request->date_to);
            }

            $invoices = $query->orderByDesc('i.id')->get();

            return response()->json([
                'status' => 'success',
                'data' => $invoices
            ]);
        } catch (\Exception $e) {
            Log::error('Invoice Index Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch invoices'
            ], 500);
        }
    }

    /**
     * Get filter options for dropdowns (invoice numbers and client names)
     */
    public function getFilterOptions()
    {
        try {
            $invoiceNumbers = DB::table('invoice')
                ->select('invoice_number')
                ->distinct()
                ->orderBy('invoice_number')
                ->pluck('invoice_number');

            $clientNames = DB::table('invoice')
                ->select('client_name')
                ->distinct()
                ->orderBy('client_name')
                ->pluck('client_name');

            return response()->json([
                'status' => 'success',
                'invoice_numbers' =>  $invoiceNumbers,
                'client_names' => $clientNames
            ]);
        } catch (\Exception $e) {
            Log::error('Filter Options Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch filter options'
            ], 500);
        }
    }

    /**
     * Create invoice with invoice_details in single transaction
     * Master-Detail pattern
     */
    public function store(StoreInvoiceRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            // Create invoice master record
            $invoiceId = DB::table('invoice')->insertGetId([
                'client_id' => $request->client_id,
                'invoice_number' => $request->invoice_number,
                'kam' => $request->kam,
                'client_name' => $request->client_name,
                'client_address' => $request->client_address,
                'company_id' => $request->company_id,
                'billing_date' => $request->billing_date,
                'subtotal' => $request->subtotal,
                'total' => $request->total,
                'note' => $request->note,
                'prepared_by' => $request->prepared_by,
                'received_by' => $request->received_by,
                'pmnt_rcv_acc_id' => $request->pmnt_rcv_acc_id,
                'pmnt_rcv_bank' => $request->pmnt_rcv_bank,
                'pmnt_rcv_acc' => $request->pmnt_rcv_acc,
                'pmnt_rcv_branch' => $request->pmnt_rcv_branch,
                'pmnt_rcv_rn' => $request->pmnt_rcv_rn,
            ]);

            // Create invoice detail records with time frames
            if (isset($request->invoice_details) && is_array($request->invoice_details)) {
                foreach ($request->invoice_details as $detail) {
                    DB::table('invoice_details')->insert([
                        'invoice_id' => $invoiceId,
                        'description' => $detail['description'],
                        'sms_qty' => $detail['sms_qty'],
                        'unit_price' => $detail['unit_price'],
                        'start_time' => $detail['start_time'] ?? null,
                        'end_time' => $detail['end_time'] ?? null,
                        'total' => $detail['total'],
                    ]);
                }
            }

            DB::commit();

            // Fetch the created invoice with details
            $invoice = DB::table('invoice')->where('id', $invoiceId)->first();
            $details = DB::table('invoice_details')->where('invoice_id', $invoiceId)->get();

            Log::info('Invoice created successfully', [
                'invoice_id' => $invoiceId,
                'invoice_number' => $request->invoice_number,
                'details_count' => count($request->invoice_details)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice created successfully',
                'invoice_id' => $invoiceId,
                'invoice' => $invoice,
                'details' => $details
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Invoice Creation Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create invoice. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 422);
        }
    }

    /**
     * Show single invoice with details
     */
    public function show($id)
    {
        try {
            $invoice = DB::table('invoice as i')
                ->leftJoin('bank_account as b', 'b.id', '=', 'i.pmnt_rcv_acc_id')
                ->select(
                    'i.*',
                    'b.account_name as bank_account_name',
                    'b.branch_name as bank_branch',
                    'b.routing_number as bank_routing_number',
                    'b.account_number as bank_account_number'
                )
                ->where('i.id', $id)
                ->first();

            if (!$invoice) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invoice not found'
                ], 404);
            }

            // Get invoice details
            $details = DB::table('invoice_details')
                ->where('invoice_id', $id)
                ->orderBy('id')
                ->get();

            // Attach details directly to invoice object
            $invoice->details = $details;

            return response()->json([
                'status' => 'success',
                'invoice' => $invoice,
                'details' => $details
            ]);

        } catch (\Exception $e) {
            Log::error('Invoice Show Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch invoice'
            ], 500);
        }
    }

    /**
     * Update invoice
     */
    public function update(UpdateInvoiceRequest $request, $id)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $invoice = DB::table('invoice')->where('id', $id)->first();
            if (!$invoice) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invoice not found'
                ], 404);
            }

            // Update invoice master record
            DB::table('invoice')->where('id', $id)->update([
                'client_id' => $request->client_id,
                'invoice_number' => $request->invoice_number,
                'kam' => $request->kam,
                'client_name' => $request->client_name,
                'client_address' => $request->client_address,
                'company_id' => $request->company_id,
                'billing_date' => $request->billing_date,
                'subtotal' => $request->subtotal,
                'total' => $request->total,
                'note' => $request->note,
                'prepared_by' => $request->prepared_by,
                'received_by' => $request->received_by,
                'pmnt_rcv_acc_id' => $request->pmnt_rcv_acc_id,
                'pmnt_rcv_bank' => $request->pmnt_rcv_bank,
                'pmnt_rcv_acc' => $request->pmnt_rcv_acc,
                'pmnt_rcv_branch' => $request->pmnt_rcv_branch,
                'pmnt_rcv_rn' => $request->pmnt_rcv_rn,
            ]);

            // Delete existing details and insert new ones
            DB::table('invoice_details')->where('invoice_id', $id)->delete();

            if (isset($request->invoice_details) && is_array($request->invoice_details)) {
                foreach ($request->invoice_details as $detail) {
                    DB::table('invoice_details')->insert([
                        'invoice_id' => $id,
                        'description' => $detail['description'],
                        'sms_qty' => $detail['sms_qty'],
                        'unit_price' => $detail['unit_price'],
                        'start_time' => $detail['start_time'] ?? null,
                        'end_time' => $detail['end_time'] ?? null,
                        'total' => $detail['total'],
                    ]);
                }
            }

            DB::commit();

            // Fetch updated invoice with details
            $updatedInvoice = DB::table('invoice')->where('id', $id)->first();
            $details = DB::table('invoice_details')->where('invoice_id', $id)->get();

            Log::info('Invoice updated successfully', [
                'invoice_id' => $id,
                'invoice_number' => $request->invoice_number,
                'details_count' => count($request->invoice_details)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice updated successfully',
                'invoice' => $updatedInvoice,
                'details' => $details
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Invoice Update Error', [
                'invoice_id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update invoice',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 422);
        }
    }

    /**
     * Delete invoice and its details
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Check if invoice exists
            $invoice = DB::table('invoice')->where('id', $id)->first();
            if (!$invoice) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invoice not found'
                ], 404);
            }

            // Delete invoice details first (child records)
            DB::table('invoice_details')->where('invoice_id', $id)->delete();

            // Delete invoice master record
            DB::table('invoice')->where('id', $id)->delete();

            DB::commit();

            Log::info('Invoice deleted successfully', ['invoice_id' => $id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Invoice Delete Error', [
                'invoice_id' => $id,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete invoice'
            ], 422);
        }
    }

    /**
     * Get bank accounts for dropdown
     */
    public function getBankAccounts()
    {
        try {
            $banks = DB::table('bank_account')
                ->select('*')
                ->orderBy('account_name')
                ->get();

            return response()->json($banks);

        } catch (\Exception $e) {
            Log::error('Bank Accounts Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch bank accounts'
            ], 500);
        }
    }
}
