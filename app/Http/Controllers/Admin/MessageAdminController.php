<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessagesEvent;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\MessageController;
use App\Models\Media;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageAdminController extends BaseController
{
    public function index(){
        $msgConroller = new MessageController();
        return $msgConroller->index();
    }

    // receiverId, text, 
    public function store(Request $req){
        $new_msg = new Message();
        $arr = [];
        if($req->text){
            $new_msg->text = $req->text;
        }
        $receiver = User::find($req->receiverId);
        $sender = auth()->user();
        $new_msg->sender()->associate($sender);
        $new_msg->receiver()->associate($receiver);
        // $new_msg->adsJob()->associate(AdsJobs::find($req->adsId));
        $new_msg->save();
        //upload files
        if($req->hasFile('medias')){
            $medias = $req->file('medias');
            foreach ($medias as $fileMedia) {
                $filename  = $fileMedia->getClientOriginalName();
                $fileMedia->move(public_path('storage/storage/uploads'), $filename);
                $media = new Media();
                $media->url = 'storage/storage/uploads/'.$filename;
                $media->message()->associate($new_msg);
                $media->save();
                unset($media->message);
                array_push($arr, $media);
            }
        }

        // upload audio
        if($req->audio){
            $media = new Media();
            $media->url = $req->audio;
            $media->message()->associate($new_msg);
            $media->save();
            $media->message = null;
            unset($media->message);
            array_push($arr, $media);
        }

        if(count($arr)){
            $new_msg->medias = $arr;
        }
        broadcast(new MessagesEvent($new_msg));
        return  $this->sendResponse($new_msg, __('messages.added_success'));
    }

    public function show(Request $req, $id){
        $msgConroller = new MessageController();
        return $msgConroller->show($req, $id, "desc");
    }
}
