<?php

use App\Http\Controllers\AdsJobsController;
use App\Http\Controllers\AdsSavedController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\SubCompanyController;
use App\Http\Controllers\StatistiqueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/', function(){
   return "hello"; 
});


Route::group(['middleware'=> ['logApi']], function (){
    Route::group(['middleware'=> ['changeLanguage']], function () {
    Route::get('/statistiques', [StatistiqueController::class, 'getStatsitique']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post("/registerAdmin", [AuthController::class, 'registerAdmin'] );
    Route::post('/socialLogin', [AuthController::class, 'socialLogin']);
    Route::post('/register', [AuthController::class, 'register']);
    });
    Route::resource('/adsJobs', AdsJobsController::class)->only('show', 'index')->middleware('changeLanguage');
    Route::get('/getCv', [CvController::class, 'index'] )->middleware('changeLanguage');
    
    Broadcast::routes(['middleware' => 'auth:sanctum']);
    
    Route::get('/download', function (Request $req) {
            return Response::download($req->name);
    })->middleware('auth:sanctum');
    
    Route::group(['middleware'=> ['auth:sanctum' ,'changeLanguage']], function () {
        require('apiAdmin.php');

        Route::post('messageSeen', [MessageController::class, 'messageSeen']);
        Route::resource('/messages', MessageController::class);
        Route::resource('/notifications', NotificationController::class);
        Route::post('/notificationSeen', [NotificationController::class, 'notificationSeen']);
        // account
        Route::get('companyInfo', [CompanyController::class, 'index']);
        Route::post('companyInfo', [CompanyController::class, 'store']);
        Route::post('subCompanyInfo', [SubCompanyController::class, 'store']);
        Route::post('subCompanyInfo/{id}', [SubCompanyController::class, 'update']);
        Route::delete('subCompanyInfo/{id}', [SubCompanyController::class, 'destroy']);
        Route::resource('/plans', PlanController::class);
        Route::get('/paginateOrders', [PlanController::class, 'paginateOrders']);
    
        Route::group(['middleware'=> ['confirmAccount']], function () {
        // midelawre confirm_account
        Route::resource('/adsJobs', AdsJobsController::class)->only('store', 'update', 'destroy');
        Route::post('/myfatorah', [PlanController::class, 'myfatorah']);
        Route::get('/statusPayment', [PlanController::class, 'statusPayment']);
        Route::post('/addThisAdsToSpecial', [PlanController::class, 'addThisAdsToSpecial']);
        // midelawre confirm_account && have ads
        Route::post('/ApplyAndCancelJob', [OfferController::class, 'ApplyAndCancelJob'])->middleware('hasAds');
        Route::post('/acceptAndRefuseOffer', [OfferController::class, 'acceptAndRefuseOffer'])->middleware('hasAds');
        });
    
        Route::get('/myAdsJobs', [AdsJobsController::class, 'myAdsJobs']);
        Route::get('/myOffers', [OfferController::class, 'myOffers']);
    
    
        Route::get('/savedAds', [AdsSavedController::class, 'mySavedAds']);
        Route::post('/adSaved', [AdsSavedController::class, 'adSaved']);
    
        Route::post('/deleteCv', [CvController::class, 'deleteCv']);
        Route::resource('/experiences', ExperienceController::class);
        Route::resource('/educations', EducationController::class);
        Route::resource('/certificates', CertificateController::class);
        Route::resource('/skills', SkillController::class);
        Route::post('/updateProfile', [PersonalController::class, 'updateProfile']);
        Route::post('/verifyEmail', [AuthController::class, 'verifyEmail']);
        Route::post('/VerifyCode', [AuthController::class, 'verifyCode']);
        Route::post('/changePassword', [AuthController::class, 'changePassword']);
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::get('/removeAccount', [AuthController::class, 'removeAccount']);
        
        Route::get('/newNotAndMsg', [NotificationController::class, 'newNotAndMsg']);
        Route::resource('/reports', ReportController::class);
    });
});


