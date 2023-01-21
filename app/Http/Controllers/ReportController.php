<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Models\AdsJobs;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;


class ReportController extends BaseController
{
    public function index(Request $req){
        $reports =  Report::with('user')->with('adJob')
        ->skip($req->skip)->take($req->take)->get();
        $total = Report::with('user')->with('adJob')
        ->skip($req->skip)->take($req->take)->count();
        return $this->sendResponse(["total" =>  $total, "data"=> $reports],__('messages.found_result'));
    }

    public function show(Request $req, $id){
        $report =  Report::where("id", $id)->with('user')->with('adJob')
        ->skip($req->skip)->take($req->take)->first();
        return $this->sendResponse($report,__('messages.found_result'));
    }

    public function store(ReportRequest $req){
        $report = new Report();
        if($req->adsId != "null"){
            $report->adJob()->associate(AdsJobs::find($req->adsId));
        }
        $report->user()->associate(User::find($req->userId));

        $report->title = $req->title;
        $report->content = $req->content;
        $notController = new NotificationController();
        $report->save();
        $notController->createReport($report);
        return $this->sendResponse($report, __('messages.added_success'));
    }
}
