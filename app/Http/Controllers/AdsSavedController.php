<?php

namespace App\Http\Controllers;

use App\Models\AdsJobs;
use App\Models\User;
use Illuminate\Http\Request;

class AdsSavedController extends BaseController
{
    public function mySavedAds(Request $req){
        $user = User::find(auth()->user()->id);
        $adsJobs = $user->savedAds()->with('user')->paginate(6);
        return $this->sendResponse($adsJobs, __('messages.found_result'));
    }



    public function adSaved(Request $req){
        $adsJob = AdsJobs::find($req->adsId);
        $res = $adsJob->saveds()->where('user_id', auth()->user()->id)->get();
        if($res->count()){
            $adsJob->saveds()->detach(auth()->user());
            return $this->sendResponse('', __('messages.remove_saved'));
        }
        $adsJob->saveds()->attach(auth()->user());
        return $this->sendResponse('', __('messages.add_saved'));
    }
    
    
    
    
    public function destroyByUser($arr){
        foreach($arr as $elm){
            $this->destroy($elm->id);
        }
    }


    public function destroy($id)
    {
        $adsJobs = AdsJobs:: find($id);
        $adsJobs->delete();
        return $this->sendResponse('', __('messages.deleted_data'));

    }
}
