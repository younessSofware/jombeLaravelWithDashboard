<?php

namespace App\Http\Controllers;

use App\Events\NotificationsEvent;
use App\Models\AdsJobs;
use App\Models\Notification;
use App\Models\Message;
use App\Models\User;
use App\Models\Order;
use App\Models\Report;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function index(){
        $notifcations =  Notification::where('user_id', auth()->user()->id)
        ->where('ads_jobs_id', '!=', 'null')
        ->with('adjobs')->get()
        ->unique('ads_jobs_id');
        $newNotify = 0;
        foreach ($notifcations as $notification) {
            $notification->setAttribute('emitter', User::find($notification->adjobs->user_id));
            if($notification->seen == 0) $newNotify++;

        }
        return $this->sendResponse(['notifcations' => $notifcations, 
        'newNotify' => $newNotify], __('messages.found_result'));

    }

    public function create($content, $ads_id, $user_id, $order_id = null){
        $notification = new Notification();
        $notification->content = $content;
        if($ads_id){
            $notification->adjobs()->associate(AdsJobs::find($ads_id));   
        }else{
            $notification->order()->associate(Order::find($order_id));
        }
        $notification->user()->associate(User::find($user_id));
        $notification->save();
        broadcast(new NotificationsEvent($notification));
        $notification = new Notification();
        $notification->content = $content;
        if($ads_id){
            $notification->adjobs()->associate(AdsJobs::find($ads_id));
            $notification->user()->associate(User::where("username", "like", "admin")->first());
            $notification->save();
            broadcast(new NotificationsEvent($notification));
        }
    }

    public function createReport($report){
        $notification = new Notification();
        $notification->content = $report->title;
        $notification->user()->associate(User::where("username", "like", "admin")->first());
        $notification->report()->associate(Report::find($report->id));
        $notification->save();
        broadcast(new NotificationsEvent($notification));
        return $notification;
    }

    public function notificationSeen(Request $req){
        if($req->id){
            Notification::where('id', $req->id)->update([
                'seen' => 1
            ]);
            return 1;
        }
        Notification::where('user_id', auth()->user()->id)->update([
            'seen' => 1
        ]); 
        return 1; 
    }
    
    public function destroyByUser($arr){
        foreach($arr as $elm){
            $this->destroy($elm->id);
        }
    }


    public function destroy($id)
    {
        $not = Notification::find($id);
        $not->delete();
        return $this->sendResponse('', __('messages.deleted_data'));
    }
    
    
    public function newNotAndMsg(){
        $nbrNewNot = Notification::where('user_id', auth()->user()->id)->where('ads_jobs_id', "!=", "null")->where('seen', 0)->count();
        $nbrNewMsg = Message::where('receiver_id', auth()->user()->id)->where('seen', 0)->count();
        
         return $this->sendResponse(['nbrNewMsg' => $nbrNewMsg, 
        'nbrNewNot' => $nbrNewNot], __('messages.found_result'));
    }
    
    
}
