<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdsJobsRequest;
use App\Models\AdsJobs;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdsJobsController extends BaseController
{
    function checkSpecialAds($adsJobs){
        //validate expiryDate for special ads
        foreach($adsJobs as $adJob){
            foreach($adJob->orders()->get() as $order){
                $data = json_decode($order->Result);
                $expriyDateAds =  new Carbon($data->ExpiryDate);
                if($expriyDateAds->lt(Carbon::now())) $adJob->isSpecial = 0;
                else $adJob->isSpecial = 1;
                $adJob->save();
            }
        }
    }
    
    function checkExpiryCoinsTime($orders){
        // $newNot = new NotificationController();
        foreach($orders as $order){
            $data = json_decode($order->Result);
            $expriyDateAds =  new Carbon($data->ExpiryCoinDate);
             if($expriyDateAds->lt(Carbon::now())){
                // if(App::getLocale() == 'en'){
                //     $content = 'Dear friend, we regret to inform you that there is less than a month left until the end';
                // }else{
                //     $content = 'صديقي العزيز يؤسفنا ان نعلمك انه متبقي اقل من شهر على النهاية';
                // }
                // $newNot->create($content, null, $order->user_id, $order->id);
                // $order->delete();
            }else{
                if($expriyDateAds->subMonth()->lt(Carbon::now())){
                  //send notification
                //   if(!Notification::where('order_id', $order->id)->count()){
                //         if(App::getLocale() == 'en'){
                //             $content = 'Dear friend, we regret to inform you that there is less than a month left until the end';
                //         }else{
                //             $content = 'صديقي العزيز يؤسفنا ان نعلمك انه متبقي اقل من شهر على النهاية';
                //         }
                //         $newNot->create($content, null, $order->user_id, $order->id);   
                //   }
                }
            }
            
        }
    }




    
    public function index(Request $req){
        $role = 0;
        if($req->type == 'f') $role = 2;
        if($req->type == 'e') $role = 3;
        if($req->special)
        $this->checkSpecialAds(AdsJobs::all());
        $this->checkExpiryCoinsTime(Order::where('ads_jobs_id', null)->get());
        $symbole = '==';
        $querySearch = '';
        if($req->querySearch) $querySearch = $req->querySearch;
        if($req->special) $symbole = '!=';
        $adsJobs = AdsJobs::whereRelation('user', 'role', '!=', $role)
                ->where('isSpecial', $symbole, 0)->where('title', 'like', '%'.$querySearch.'%')
                ->with('user')->skip($req->page)
                ->paginate(6);
        return $this->sendResponse($adsJobs, __('messages.found_result'));
    }

    public function myAdsJobs(Request $request){
        // return  $request->isSpecial;
        if(isset($request->isSpecial)) $adsJobs = AdsJobs::where('user_id', auth()->user()->id)->where('isSpecial', false)->with('offers')->paginate(6);
        else $adsJobs = AdsJobs::where('user_id', auth()->user()->id)->with('offers')->paginate(6);
        foreach ($adsJobs as $adJob) {
            $adJob->setAttribute('nbrOffers', $adJob->offers()->count());
        }
        return $this->sendResponse($adsJobs, __('messages.found_result'));
    }

    public function show($id){
        $nbrSaved = 0;
        $nbrOffer = 0;
        $adJob = AdsJobs::with('user')->find($id);
        if(auth()->user()) $nbrOffer = $adJob->offers()->where('user_id', auth()->user()->id)->count();
        $adJob->setAttribute('isApply', false);
        if($nbrOffer){
            $adJob->setAttribute('isApply', true);
        }
        $arr = array();
        foreach ($adJob->offers()->get() as $offer) {
            $x = $offer->adsJobs()->with('user')->first();
            $x->setAttribute('statusOffer', DB::table('offers')->where('ads_jobs_id', $id)
            ->where('user_id', $x->user_id)->get('status')->first()->status); 
            array_push($arr, $x);
        }
        if(auth()->user()) $nbrSaved = DB::table('savedjobs')->where('ads_jobs_id', $id)->where('user_id', auth()->user()->id)->count();
        $adJob->setAttribute('isSaved', $nbrSaved);
        $adJob->setAttribute('offers', $arr);

        if(auth()->user() && auth()->user()->role == 2 && DB::table('offers')->where('ads_jobs_id', $id)->where('user_id', auth()->user()->id)->get('status')->count()){
            $adJob->setAttribute('statusOffer', DB::table('offers')->where('ads_jobs_id', $id)->where('user_id', auth()->user()->id)->get('status')->first()->status);
        }
        return $this->sendResponse($adJob, __('messages.found_result'));

    }

    
    public function store(AdsJobsRequest $request){
        $adsJobs = new AdsJobs($request->all());
        if($request->username){
            $adsJobs->user()->associate(User::where('username', $request->username)->first());
        }else $adsJobs->user()->associate(auth()->user())->save();
        return $this->sendResponse($adsJobs, __('messages.save_data'));
    }

    public function update(AdsJobsRequest $request, $id){
        $adJob = AdsJobs::with('user')->find($id);
        $adJob->update($request->all());
        return $this->sendResponse($adJob, __('messages.edited_success'));
    }
    
    public function destroyByUser($arr){
        foreach($arr as $elm){
            $this->destroy($elm->id);
        }
    }


    public function  destroy($id){
        $adsJobs = AdsJobs::with('user')->find($id);
        $pffers = $adsJobs->offers()->get();
        foreach ($pffers as $offer) {
            $adsJobs->offers()->detach($offer->user_id);
        }
        $adsJobs->delete();
        return $this->sendResponse('', __('messages.deleted_data'));
    }
}
