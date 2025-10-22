<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
class Invoice2Controller extends Controller
{
    /**
     * Fetch support data (clients)
     */
    public function getsupportdata(Request $request)
    {
        try {
            $senderUrl = env('SENDER_API_URL', 'http://localhost:82/api/support-data');
            $apiKey = env('SENDER_API_KEY', 'asad1947!52!71!24');

            Log::info('Fetching support data via GET', ['url' => $senderUrl]);

            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->get($senderUrl);

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
     * Get all invoices with details
     */
    public function index()
    {
        try {
            $invoices = DB::table('invoice as i')
                ->leftJoin('bank_account as b', 'b.id', '=', 'i.pmnt_rcv_acc_id')
                ->select(
                    'i.*',
                    'b.account_name as bank_account_name',
                    'b.branch_name as bank_branch',
                    'b.routing_number as bank_routing_number',
                    'b.account_number as bank_account_number'
                )
                ->orderByDesc('i.id')
                ->get();

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
     * Create invoice with invoice_details in single transaction
     * Master-Detail pattern
     */
    public function store(StoreInvoiceRequest $request)
    {
        // Remove timestamps from validation since table doesn't have them
       $validated = $request->validated();

        DB::beginTransaction();

        try {
            // Create invoice master record - WITHOUT timestamps
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

            // Create invoice detail records - WITHOUT timestamps
            if (isset($request->invoice_details) && is_array($request->invoice_details)) {
                foreach ($request->invoice_details as $detail) {
                    DB::table('invoice_details')->insert([
                        'invoice_id' => $invoiceId,
                        'description' => $detail['description'],
                        'sms_qty' => $detail['sms_qty'],
                        'unit_price' => $detail['unit_price'],
                        'total' => $detail['total'],
                        // REMOVED: 'created_at' => now(),
                        // REMOVED: 'updated_at' => now(),
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
            'details' => $details  // Also available separately
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
     * Update invoice with details
     */
    public function update (UpdateInvoiceRequest $request, $id)
    {
       $validated = $request->validated();

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

            // Update invoice master record - WITHOUT updated_at
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
                // REMOVED: 'updated_at' => now(),
            ]);

            // Delete existing invoice details
            DB::table('invoice_details')->where('invoice_id', $id)->delete();

            // Insert new invoice details - WITHOUT timestamps
            if (isset($request->invoice_details) && is_array($request->invoice_details)) {
                foreach ($request->invoice_details as $detail) {
                    DB::table('invoice_details')->insert([
                        'invoice_id' => $id,
                        'description' => $detail['description'],
                        'sms_qty' => $detail['sms_qty'],
                        'unit_price' => $detail['unit_price'],
                        'total' => $detail['total'],
                        // REMOVED: 'created_at' => now(),
                        // REMOVED: 'updated_at' => now(),
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
