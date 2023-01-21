<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Plan;
use App\Models\AdsJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use MyFatoorah\Library\PaymentMyfatoorahApiV2;
use Carbon\Carbon;
use DateTime;

class PlanController extends BaseController
{
    public $mfObj;

    /**
     * create MyFatoorah object
     */
    public function __construct() {
        $this->mfObj = new PaymentMyfatoorahApiV2(config('myfatoorah.api_key'), config('myfatoorah.country_iso'), config('myfatoorah.test_mode'));
    }
    public function index(Request $request){
        $str = '';
        if(app()->getLocale() == 'en') $str ="_en";
        $plans = Plan::select("id", "name$str as name", "description$str as description", "period$str as period", 'price')->get();
        $response = [
            'plans' => $plans,
            'orders' => Order::with('adJob')->where('user_id', auth()->user()->id)->skip(1)->paginate(5)
        ];
        if($request->all == true){
            $response['orders'] = Order::with('adJob')->where('user_id', auth()->user()->id)->get();
        }
        
        
        $response['balance'] = Order::where('user_id', auth()->user()->id)->where('ads_jobs_id', null)->sum('item_name');
        return $this->sendResponse($response, __('retreive.correctly'));
    }
    
    public function paginateOrders(Request $req){
        $orders = Order::with('adJob')->where('user_id', auth()->user()->id)->skip($req->page)->paginate(5);
        return $this->sendResponse($orders, __('retreive.correctly'));
    }

    public function myfatorah(Request $req){
        // try {
            $paymentMethodId = 0; // 0 for MyFatoorah invoice or 1 for Knet in test mode
            // return $this->getPayLoadData($req->plan_id, $req->plan_count);
            $data = $this->mfObj->getInvoiceURL($this->getPayLoadData($req->plan_id, $req->plan_count)
            ,$paymentMethodId);
            

            //return response()->json(['IsSuccess' => 'true', 'Message' => 'Invoice created successfully.', 'Data' => $data]);
            return $this->sendResponse($data, "Invoice created successfully");
        // } catch (\Exception $e) {
        //     // return response()->json(['IsSuccess' => 'false', 'Message' => $e->getMessage()]);
        //     return $this->sendError('Message Sended', __($e->getMessage()));

        // }
    }

    private function getPayLoadData($orderId = null, $plan_count = 0) {
        $callbackURL = 'https://jobme.me/#/base/accounts/buy-coins';
        // $callbackURL = 'http://127.0.0.1:4200/#/base/accounts/buy-coins';
        $user = auth()->user();
        $plan = Plan::find($orderId);
        $invoiceItems[] = [
            'ItemName'  => app()->getLocale() == 'ar' ? $plan->name : $plan->name_en, //ISBAN, or SKU
            'Quantity'  => number_format($plan_count), //Item's quantity
            'UnitPrice' => number_format($plan->price), //Price per item
        ];

        return [
            'CustomerName'       => $user->fullname,
            'InvoiceValue'       => number_format($plan->price)*number_format($plan_count),
            'DisplayCurrencyIso' => 'USD',
            'CustomerEmail'      => $user->email,
            'CallBackUrl'        => $callbackURL,
            'ErrorUrl'           => $callbackURL,
            'MobileCountryCode'  => '+966',
            'CustomerMobile'     => '0000000000',//$user->phone,
            'Language'           => app()->getLocale() == 'ar' ? 'ar':'en',
            'CustomerReference'  => $orderId,
            'UserDefinedField'   => $user->id,
            'SourceInfo'         => 'JobMe App',
            'InvoiceItems'       => $invoiceItems
        ];
    }

    public function statusPayment(Request $request) {
        // try {
        
            $nbr_order = Order::where('payment_id', $request->paymentId)->count();
            if($nbr_order)
            return $this->sendError('Message Sended', __('error'), 400);
            $data = $this->mfObj->getPaymentStatus($request->paymentId, 'PaymentId');
            if ($data->InvoiceStatus == 'Paid') {
                $msg = 'Invoice is paid.';
            } else if ($data->InvoiceStatus == 'Failed') {
                $msg = 'Invoice is not paid due to ' . $data->InvoiceError;
                $error = ['Message' => $msg, 'status' => 0];
                return $this->sendError('Message Sended', $error, 400);
            } else if ($data->InvoiceStatus == 'Expired') {
                $msg = 'Invoice is expired.';
                $error = ['message' => $msg, 'status' => 2];
                return $this->sendError('Message Sended', $error, 400);

            }
            $i=0;
            while($i<$data->InvoiceItems[0]->Quantity){
                $order = new Order();
                // $order->id = 1;
                $order->user_id = $data->UserDefinedField;
                $order->plan_id = $data->CustomerReference;
                $order->invoice_id = $data->InvoiceId;
                $order->invoice_status = $data->InvoiceStatus;
                $order->payment_id = $data->InvoiceTransactions[0]->PaymentId;
                $order->item_name = $data->InvoiceItems[0]->ItemName;
                $order->quantity = 1;
                $order->unit_price = $data->InvoiceItems[0]->UnitPrice;
                $order->invoice_value = $data->InvoiceValue;
                $nextYear = Carbon::now();
                $data->ExpiryCoinDate = $nextYear->addYear(1);
                $order->Result = json_encode($data, true);
                $order->ads_jobs_id = null;
                $order->save();
                $i++;
            }
            return $this->sendResponse($data,  __('messages.paid_done'));
        // } catch (\Exception $e) {
            // return $this->sendError('Message Sended', __('messages.paid_error'));
        // }
    }
    
    public function addThisAdsToSpecial(Request $req){
        $ads_id = $req->adsId;
        $order_id = $req->orderId;
        $order = Order::find($order_id);
        $data = json_decode($order->Result);
        $now = Carbon::now();
        $data->ExpiryDate = $now->addDay($this->getDayAdd($order));
        $data->ExpiryTime = explode(" ", $now->toDateTimeString())[1];
        $order->Result = json_encode($data);
        $order->adJob()->associate($ads_id);
        $ads = AdsJobs::find($ads_id);
        $ads->isSpecial = 1;
        $ads->save();
        $order->save();
        $order->adJob = $ads;
        return $this->sendResponse($order,  __('messages.paid_done'));
    }
    
    public function getDayAdd($order){
        return (int)filter_var($order->item_name, FILTER_SANITIZE_NUMBER_INT);
        // return 
    }
    
    public function destroyByUser($arr){
        foreach($arr as $elm){
            $this->destroy($elm->id);
        }
    }
    
    public function destroy($id){
        $plan = Order::find($îd);
        $plan->delete();
    }


}
