<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdsJobsController;
use App\Models\AdsJobs;
use App\Models\Order;
use Illuminate\Http\Request;

class AdsJobsAdminController extends AdsJobsController
{
        // /admin/ads
        public function getAll(Request $req){
            $searchQuery = urldecode($req->searchQuery);
            $searchQuery = json_decode($searchQuery);

            if($searchQuery->isSpecial)
            $this->checkSpecialAds(AdsJobs::all());
            $this->checkExpiryCoinsTime(Order::where('ads_jobs_id', null)->get());
            // return $searchQuery;
            if($searchQuery->id == ""){
                $total = AdsJobs::whereRelation('user', 'role', 'like', $searchQuery->role)
                ->whereRelation('user', 'fullname', 'like', "%$searchQuery->fullname%")
                ->where('title', 'like', "%$searchQuery->title%")
                ->where('isSpecial', "like", $searchQuery->isSpecial)
                ->with('user')->count();

        $adsJobs = AdsJobs::whereRelation('user', 'role', 'like', $searchQuery->role)
                ->whereRelation('user', 'fullname', 'like', "%$searchQuery->fullname%")
                ->where('title', 'like', "%$searchQuery->title%")
                ->where('isSpecial', "like", $searchQuery->isSpecial)
                ->with('user')->skip($req->skip)->take($req->take)->get();
            }else{
                $total = AdsJobs::whereRelation('user', 'role', 'like', $searchQuery->role)
                ->whereRelation('user', 'fullname', 'like', "%$searchQuery->fullname%")
                ->where('title', 'like', "%$searchQuery->title%")
                ->where('isSpecial', "like", $searchQuery->isSpecial)
                ->where("id", "$searchQuery->id")
                ->with('user')->count();

        $adsJobs = AdsJobs::whereRelation('user', 'role', 'like', $searchQuery->role)
                ->whereRelation('user', 'fullname', 'like', "%$searchQuery->fullname%")
                ->where('title', 'like', "%$searchQuery->title%")
                ->where('isSpecial', "like", $searchQuery->isSpecial)
                ->where("id", "$searchQuery->id")
                ->with('user')->skip($req->skip)->take($req->take)->get();
            }

            $data = [ "total" =>  $total, "data"=> $adsJobs];
            return $this->sendResponse($data, __('messages.found_result'));
        }

}
