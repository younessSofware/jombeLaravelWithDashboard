<?php

namespace App\Http\Controllers;

use App\Models\AdsJobs;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Offer;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CvController extends BaseController
{
    // get my cv or get cv of freelancer by account company and status between us
    public function index(Request $request){
        $ads_id = null;
        if($request->user_id){
            $user_id = $request->user_id;
            $ads_id = $request->my_ads_id;
        }
        $res = [
                'Education' => Education::where('user_id', $user_id)->get(),
                'Experiences' => Experience::where('user_id', $user_id)->get(),
                'Certificates' => Certificate::where('user_id', $user_id)->get(),
                'Skills' =>  Skill::where('user_id', $user_id)->get(),
                'user' => User::with('adsJobs')->find($user_id),
        ];
        if($ads_id){
            $res += ['user_id'=>$user_id,'ads_id' => $ads_id];
            $res += ['status_job' => Offer::where('user_id', $user_id)->where('ads_jobs_id', $ads_id)->first('status')->status];
        }
        // saved jobs
        if(auth()->user() &&  auth()->user()->role != 1 && $user_id != auth()->user()->id){
            $id = AdsJobs::where('user_id', $user_id)->first('id')->id;
            $nbrSaved = DB::table('savedjobs')->where('ads_jobs_id', $id)->where('user_id', auth()->user()->id)->count();
            $res += ['isSaved' => $nbrSaved];
        }
        return $this->sendResponse($res, __('messages.found_result'));
    }

    public function deleteCv(Request $request){
        $id = $request->id;
        $screen = $request->screen;
        if($screen == 'certificates'){
            $certificateController = new CertificateController();
            $certificateController->destroy($id);
            return $this->sendResponse('', __('messages.deleted_data'));
        }
        if($screen == 'educations'){
            $educationController = new EducationController();
            $educationController->destroy($id);
            return $this->sendResponse('', __('messages.deleted_data'));

        }
        if($screen == 'experiences'){
            $experienceController = new ExperienceController();
            $experienceController->destroy($id);
            return $this->sendResponse('', __('messages.deleted_data'));

        }

        if($screen == 'skills'){
            $skillController = new SkillController();
            $skillController->destroy($id);
            return $this->sendResponse('', __('messages.deleted_data'));

        }
    }
}
