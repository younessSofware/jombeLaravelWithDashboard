<?php

namespace App\Http\Controllers;

use App\Events\MessagesEvent;
use App\Events\NotificationsEvent;
use App\Models\AdsJobs;
use App\Models\Message;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class OfferController extends BaseController
{
    public function myOffers(){
        $user = User::find(auth()->user()->id);
        $adsJobs = $user->offers()->paginate(5);
        foreach ($adsJobs as $adJob) {
            $adJob->setAttribute('nbrOffers', $adJob->offers()->count());
            $adJob->setAttribute('status', DB::table('offers')->where('ads_jobs_id', $adJob->id)->where('user_id', auth()->user()->id)->first('status')->status);
        }
        return $this->sendResponse(['ads' => $adsJobs, 'nbrAds' => $user->adsJobs()->count()], __('messages.found_result'));
    }
//Request $ads_id
    public function ApplyAndCancelJob(Request $req){
        $adsJob = AdsJobs::find($req->ads_id);
        // remove my offer on this ad
        // $res = $adsJob->offers()->where('user_id', auth()->user()->id)->get();
        // if($res->count()){
        //     $adsJob->offers()->detach(auth()->user());
        //     return $this->sendResponse($adsJob->offers()
        //     ->where('user_id', auth()->user()->id)->get(), __('applyJob.correctly'));
        // }
        $notificationConntroller = new NotificationController();
        // $content, $ads_id, $user_id
        if(App::getLocale() == 'en'){
            $content = 'he has apply of this job';
        }else{
            $content = 'لقد تم التقدم لهذه الوظيفة';
        }
        $notificationConntroller->create($content, $adsJob->id, $adsJob->user_id);
        $adsJob->offers()->attach(auth()->user());
        return $this->sendResponse('', __('messages.request_post_job'));
    }

    public function acceptAndRefuseOffer(Request $req){
        $offer = Offer::where('user_id',$req->user_id)
                        ->where('ads_jobs_id', $req->adsId)->first();
        if(!$offer){
            // choose freelancers
            $adsJob = AdsJobs::find($req->adsId);
            $adsJob->offers()->attach($req->user_id);
            $offer = Offer::where('user_id',$req->user_id)
            ->where('ads_jobs_id', $req->adsId)->first();
        }
        $messageController = new MessageController();
        if($offer->status == 1){
            $messageController->blockMessaegs($req->adsId);
            $offer->status = 0;
        }else if($offer->status == 2 || $offer->status == 0){
            $messageController->createNewDiscussionFromOffer($req->user_id, $req->adsId);
            $offer->status = 1;
            $offer->save();
            return $this->sendResponse(['offer' => $offer, 'receiver_id' => $req->user_id], __('messages.choose_client_for_job'));
        }
        $offer->save();
        return $this->sendResponse(['offer' => $offer], __('messages.refuse_client_for_job'));
    }
    
    public function destroyByUser($arr){
        foreach($arr as $elm){
            $this->destroy($elm->id);
        }
    }


    public function destroy($id)
    {
        $offer = DB::table('offers')::find($id);
        $offer->delete();
        return $this->sendResponse('', __('messages.deleted_data'));
    }

}
