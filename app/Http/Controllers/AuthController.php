<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistreRequest;
use App\Http\Requests\SocialLoginRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Mail\Subscribe;
use App\Mail\TestMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Storage;
use Carbon\Carbon;
use File;
class AuthController extends BaseController
{

    public function login(LoginRequest $request){
        
        $user = User::where('email', $request['username'])->first();
        if(!$user) $user = User::where('username', $request['username'])->first();

        // return $request->all();
        if(!$user || !Hash::check($request['password'], $user->password)){

        return $this->sendError('', __('auth.failed'), 400);
        }
        $token = $user->createToken('jobMeProject')->plainTextToken;
        $response = [
            'Result' => [
                'user' => $user,
                'token' => $token
            ],
        ];
        return response($response, 201);
    }

    public function socialLogin(SocialLoginRequest $request){

        $user = User::where('email', $request->email)->first();
        if($user){
            //social login
            // $user->update($request->all());
            $contents = file_get_contents($user->photo);
            $storage_disk = 'public';
            $filename  = $request->fullname."-".Carbon::now();
            Storage::disk($storage_disk)->put('uploads/'.$filename.'.png', $contents);
            if(!$user->photo) $user->photo = 'storage/uploads/'.$filename.'.png';
            if($user->verifyCode) $user->verifyCode = 123456;
            $user->save();
            $token =  $this->getToken($user);
            $response = [
                'Result' => [
                    'user' => $user,
                    'token' => $token
                ],
            ];
            return response($response, 201);
        }else{

            // new account;
            $user = new User($request->all());
            $user->password = bcrypt($request->social_id);
            $user->username = $request->fullname;
            $user->social_type = $request->social_type;
            $user->social_id = $request->social_id;
            $contents = file_get_contents($user->photo);
            $storage_disk = 'public';
            $filename  = $request->fullname."-".Carbon::now();
            $user->verifyCode = 123456;
            $user->	email_verified_at = Carbon::now();
            Storage::disk($storage_disk)->put('uploads/'.$filename.'.png', $contents);
            $user->photo = 'storage/uploads/'.$filename.'.png';
            $user->save();
            $token =  $this->getToken($user);
            $response = [
                'Result' => [
                    'user' => $user,
                    'token' => $token
                ]
            ];
            return response($response, 201);
        }
        return $request;
    }


    public function registerAdmin(){
        $user = new User();
        $user->username = "administrateur";
        $user->email =  "admin@admin.com";
        $user->password = bcrypt("123456789");
        $user->role = 1;
        $user->save();
        return $user;
    }


    public function register(RegistreRequest $request){
        // return $request->all();
        $user = new User($request->all());

        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->save();
        if($user->role == 3){
            $company = new Company();
            $company->user()->associate($user)->save();
        }
        $token =  $this->getToken($user);
        $response = [
            'Result' => [
                'user' => $user,
                'token' => $token
            ]
        ];
        return $this->sendResponse($response, __('messages.register_success'));
    }


    public function verifyEmail(VerifyEmailRequest $request){
        $email = auth()->user()->email;
        if($email != $request->email){
            return $this->sendError(__('messages.error_send_code'),['email' => [__('messages.send_code_email_error')]],400);

        }
        $user = User::find(auth()->user()->id);
        $user->verifyCode = mt_rand(100000, 999999);
        if ($user) {
            $res = Mail::to($email)->send(new Subscribe($email, $user->verifyCode));
            $user->save();
            return $this->sendResponse('Message Sended', __('messages.send_code_email_success'));
        }else{
            return $this->sendError(__('messages.error_send_code'),['email' => [__('messages.error_send_code')]],400);
        }
    }


    public function verifyCode(VerifyCodeRequest $request){
        $user = User::find(auth()->user()->id);
        if($user->verifyCode == $request->code){
            $user->email_verified_at = strtotime(now()) + 300;
            $user->save();
            return $this->sendResponse($user, __('messages.activeted_success'));
         }else{
            return $this->sendError(__('messages.code_error'),['code' => [__('messages.code_error')]],400);
         }

    }


    public function changePassword(ChangePasswordRequest $request){
        $user = User::find(auth()->user()->id);
        $user->password =  bcrypt($request->new_password);
        $user->save();
        return $this->sendResponse($user, __('password_changed .correctly'));

    }

    public function logout(){
        return 1;
    }

   function getToken($user){
        $token = $user->createToken('jobMeProject')->plainTextToken;
        return $token;
   }
   
   function removeAccount($userId=null){
       $id = auth()->user()->id;
       if($userId)  $id = $userId;
       $user = User::find($id);
       $certificateController = new CertificateController();
       $certificateController->destroyByUser($user->certificates()->get());
       
       $subCompanyController = new SubCompanyController();
       $subCompanyController->destroyByUser($user->subCompanies()->get());
        //   $user->subCompanies()->detach();
        
       $companyController = new CompanyController();
       $companyController->destroy();
        
       $educationController = new EducationController();
       $educationController->destroyByUser($user->educations()->get());
        //   $user->educations()->detach();
        
       $experienceController = new ExperienceController();
       $experienceController->destroyByUser($user->experiences()->get());
        //   $user->educations()->detach();
    
       $skillController = new SkillController();
       $skillController->destroyByUser($user->skills()->get());
        //   $user->skills()->detach();
    
       $notController = new NotificationController();
       $notController->destroyByUser($user->notifications()->get());
       //  $user->notifications()->detach();
       
       $adsSavedController = new AdsSavedController();
       $adsSavedController->destroyByUser($user->savedAds()->get());
       //  $user->savedAds()->detach();
       
       $orderPlanController = new PlanController();
       $orderPlanController->destroyByUser($user->orders()->get());
       //   $user->orders()->detach();
       
       $msgController = new MessageController();
       $msgController->destroy();
       
       $offerController = new OfferController();
       $offerController->destroyByUser($user->offers()->get());
       //$user->offers()->detach();
       
       $ads = new AdsJobsController();
       $ads->destroyByUser($user->adsjobs()->get());
        //   $user->adsJobs()->detach();
        
        $url = parse_url($user->photo);
        $user->delete();
       return $this->sendResponse($user, __('messages.deleted_data'));
   }
   
}
