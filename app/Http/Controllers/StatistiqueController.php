<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdsJobs;
use App\Models\User;
class StatistiqueController extends BaseController
{
    public function getStatsitique(){
        $statistiques = array();
        $statistiques['freelancers'] = User::where('role', 2)->count();
        $statistiques['jobs_free'] = AdsJobs::count();
        $statistiques['personals_files'] = User::count();
        $statistiques['companies'] = User::where('role', 3)->count();
        return $this->sendResponse($statistiques, __('messages.edited_succes'));
    }

    public function getStatistiqueAdmin(){
        $statistiques = array();
        $statistiques['freelancers'] = User::where('role', 2)->count();
        $statistiques['ads_jobs'] = AdsJobs::count();
        $statistiques['employees'] = User::where('role', 3)->count();
        return $this->sendResponse($statistiques, __('messages.edited_succes'));
    }
}