<?php

namespace App\Repositories;

use App\Jobs\SendSmsJob;
use App\Enum\PaginationEnum;
use App\Contracts\CartInterface;
use App\Services\ShortLinkService;
use Illuminate\Support\Facades\DB;
use App\Models\Configuration\Order;
use App\Models\Configuration\OrderStatus;
use App\Models\Configuration\PaymentMethod;
use App\Models\Configuration\PaymentStatus;
use App\Models\Configuration\ShipmentStatus;
use Illuminate\Http\Exceptions\HttpResponseException;

class CartRepository implements CartInterface
{

    public function paginate($request)
    {
        [$sort_field, $sort_order] = processOrderBy('id', 'DESC', null, $request->sort['column'] ?? null,  $request->sort['order'] ?? null);

        $data = Order::when(isset($sort_field), function ($query) use ($sort_field, $sort_order) {
            return $query->orderBy($sort_field, $sort_order);
        })
        ->with(['order_detail' => function($query){
            $query->with('product:id,name,description,file_path');
        },
        'payment_method','payment_status','shipment_status', 'order_status'])
        ->when(request()->filled('order_number'), function ($query) {
            $query->where("order_number", request("order_number"));
        })
        ->when(request()->filled('shipment_status_id'), function ($query) {
            $query->where("shipment_status_id", request("shipment_status_id"));
        })
        ->when(request()->filled('payment_method_id'), function ($query) {
            $query->where("payment_method_id", request("payment_method_id"));
        })
        ->when(request()->filled('payment_status_id'), function ($query) {
            $query->where("payment_status_id", request("payment_status_id"));
        })
        ->when(request()->filled('order_status_id'), function ($query) {
            $query->where("order_status_id", request("order_status_id"));
        })
        ->when(request()->filled('start_date'), function ($query) {
            $query->whereDate("created_at", '>=', request('start_date'));
        })
        ->when(request()->filled('end_date'), function ($query) {
            $query->whereDate("created_at", '<=', request('end_date'));
        })
        ->paginate(PaginationEnum::$DEFAULT);

        $data = [
            'paginator' => getFormattedPaginatedArray($data),
            'data' => $data->items(),
        ];

        return $data;
    }

    public function show($id)
    {
        $data = Order::where('id', $id)
                ->with(['order_detail' => function($query){
                    $query->with('product:id,name,description,file_path');
                },
                'payment_method','payment_status','shipment_status'])
                ->first();
        return $data;
    }



    public function update($request)
    {
        DB::beginTransaction();
        try {
            $prevstate_order = Order::where('id', $request->id)->first();
            Order::findOrFail($request->id)
            ->update([
                    'delivery_charge' => $request->delivery_charge ?? 0,
                    'paid_amount' => $request->paid_amount ?? 0,
                    'due' => $request->due ?? 0,

                    'payment_method_id' => $request->payment_method_id ?? 1, // 1 Cash On Delivery

                    'shipment_status_id' => $request->shipment_status_id ?? null,
                    'shipment_date' => $request->shipment_date ?? null,

                    'payment_status_id' => $request->payment_status_id ?? null,
                    'payment_date' => $request->payment_date ?? null,

                    'order_status_id' => $request->order_status_id ?? null,  // processing

                    'updated_at' => getNow(),
                    'updated_by' => auth()->user()->id,
                ]);

            DB::commit();

            $data = Order::with(['order_detail' => function($query){
                        $query->with('product:id,name,description,file_path');
                    },
                    'payment_method','payment_status','shipment_status'])
                    ->where('id', $request->id)->first();


            $this->sendNotification($prevstate_order, $data);


            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'debug');
            throw new HttpResponseException($this->set_response(null,  422,'error', ['Something went wrong. Please try again later!']));
        }
    }

    private function sendNotification($prevstate_order, $order)
    {
        $shortener = new ShortLinkService();
        $shortUrl = $shortener->generate('https://lyricistsassociationbd.com/tracker?order_id='.$order->order_number);

        // Check if the order status has changed
        if ($prevstate_order->order_status_id != $order->order_status_id) {

            // Define the message based on the new order status
            $messageMap = [
                2 => 'Your order '.$order->order_number.' is on the way and will arrive soon! Track your order '.$shortUrl.' Lyricist Association BD',
                3 => 'We\'re sorry, your order '.$order->order_number.' has been cancelled. Track your order '.$shortUrl.' Lyricist Association BD',
                4 => 'Your order '.$order->order_number.' has been successfully delivered. Enjoy your purchase! Track your order '.$shortUrl.' Lyricist Association BD',
            ];
            $message = $messageMap[$order->order_status_id] ?? 'Track your order '.$shortUrl.' Lyricist Association BD';

            // Dispatch the job to send the SMS
            SendSmsJob::dispatch(
                preprocess_text_for_phone_number_bd($order->phone),
                $message,
                'false'
            );
        }
    }




    public function filterData($request)
    {
        $order_number_list = Order::select('id as value', 'order_number as label')->distinct()->get();
        $payment_method_list = PaymentMethod::select('id as value', 'payment_method as label')->distinct()->get();
        $payment_status_list = PaymentStatus::select('id as value', 'status as label')->distinct()->get();
        $order_status_list = OrderStatus::select('id as value', 'status as label')->distinct()->get();
        $shipment_status_list = ShipmentStatus::select('id as value', 'status as label')->distinct()->get();
        $data = [
            'order_number_list' => $order_number_list,
            'payment_method_list' => $payment_method_list,
            'payment_status_list' => $payment_status_list,
            'order_status_list' => $order_status_list,
            'shipment_status_list' => $shipment_status_list,
        ];

        return $data;
    }

    private function generateOrderNumber()
    {
        $order_date_ddmmyy = YmdToDDMMYY(getNow());
        $order_number = $order_date_ddmmyy.'-';
        $order_numbers = DB::table('order')->whereDate('created_at', getToday())->count('id');
        $order_numbers_plus_one = str_pad($order_numbers+1, 4, '0', STR_PAD_LEFT);

        $order_number .= $order_numbers_plus_one;

        return $order_number;
    }


    public function paymentMethodsData($request)
    {
        $data = PaymentMethod::get();
        return $data;
    }
}
