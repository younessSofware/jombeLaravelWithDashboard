<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\NotificationController;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationAdminController extends BaseController
{
    public function index(Request $req){
        $notifcations =  Notification::with('user')->with("report")->with('adjobs')->where('user_id', auth()->user()->id)
        ->skip($req->skip)->take($req->take)->get();
        $total = Notification::with('user')->with("report")->with('adjobs')
        ->where('user_id', auth()->user()->id)->count();
        return $this->sendResponse(["total" =>  $total, "data"=> $notifcations],__('messages.found_result'));

    }
    // ids = [user_ids]
    public function store(Request $req){
        $notController = new NotificationController();
        if($req->global){
            $users = User::where("role", "!=", 1)->get();
            foreach ($users as $user) {
                $notController->create($req->content, Null, $user->id);  
            }
        }else{
            foreach ($req->usernams as $username) {
                $user = User::where("username", $username)->first();
                $notController->create($req->content, Null, $user->id);  
            }
        }

        return $this->sendResponse('', __('messages.save_data'));
    }

    public function update(Request $request, $id){
        $not = Notification::find($id);
        $not->update($request->all());
        return $this->sendResponse($not, __('messages.edited_success'));
    }

    public function destroy($id){
        $not = Notification::find($id);
        $not->delete();
        return $this->sendResponse($not, __('messages.deleted_data'));
    }

}
