<?php

namespace App\Http\Controllers;

use App\Events\MessagesEvent;
use App\Models\AdsJobs;
use App\Models\Media;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class MessageController extends BaseController
{
    public function index(){
        // show list messages
        $chats = Message::where(function ($query){
            $query->where('sender_id', '=', auth()->user()->id)
            ->orWhere('receiver_id', '=', auth()->user()->id);
        })
        ->orderBy('created_at', 'desc')
        ->with('receiver')
        ->with('adsJob')
        ->with('sender')
        ->with('medias')
        ->paginate(5)
        ->unique('ads_jobs_idE');
        $arr = [];
        $arr_s = [];
        $arr_r = [];
        $new = 0;
        foreach($chats as $chat){
            if(!in_array($chat->sender_id, $arr_r) && !in_array($chat->receiver_id, $arr_s)){
                array_push($arr, $chat);
            }
            if($chat->sender_id == auth()->user()->id){
                array_push($arr_r, $chat->receiver_id);
                continue;
            }
            if($chat->receiver_id == auth()->user()->id){
                array_push($arr_s, $chat->sender_id);
            }
        }

        foreach ($arr as $elm) {
            if($elm->seen == 0 && $elm->sender_id != auth()->user()->id) $new++;
        }
        return $this->sendResponse(['messages' => $arr, 'new' => $new], __('messages.found_result'));
    }
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
        $new_msg->adsJob()->associate(AdsJobs::find($req->adsId));
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

    public function show(Request $req, $id, $order="asc"){
        $adsId = Null;
        if($req->adsId != 'null'){
            $adsId = $req->adsId;
        }
        $chats = Message::where(function ($query) use($id){
            $query->where('sender_id', '=', auth()->user()->id)
            ->where('receiver_id', '=', $id)
            ->orWhere('sender_id', '=', $id)
            ->where('receiver_id', '=', auth()->user()->id);
        })
        ->where('ads_jobs_id', $adsId)
        ->orderBy('updated_at', $order)
        ->with('receiver')
        ->with('sender')
        ->with('adsJob')
        ->with('medias')
        ->skip($req->skip)->take($req->take)->get();

        $total = Message::where(function ($query) use($id){
            $query->where('sender_id', '=', auth()->user()->id)
            ->where('receiver_id', '=', $id)
            ->orWhere('sender_id', '=', $id)
            ->where('receiver_id', '=', auth()->user()->id);
        })
        
        ->where('ads_jobs_id', $adsId)
        ->orderBy('updated_at', 'asc')
        ->with('receiver')
        ->with('sender')
        ->with('adsJob')
        ->with('medias')
        ->count();
        $data = [ "total" =>  $total, "data"=> $chats];
        return $this->sendResponse($data , __('retreive.found_result'));

    }
    
    

    public function messageSeen(Request $req){
        Message::where(function ($query) use($req){
            $query->where('sender_id', '=', auth()->user()->id)
            ->where('receiver_id', '=', $req->id);
        })->update([
            'seen' => 1
        ]); 
        Message::where(function ($query) use($req){
            $query->where('receiver_id', '=', auth()->user()->id)
            ->where('sender_id', '=', $req->id);
        })->update([
            'seen' => 1
        ]);
        return $this->sendResponse([], __('paid_done.found_result'));
    }

    public function createNewDiscussionFromOffer($receiver_id, $ads_id){
        if(Message::where('ads_jobs_id', $ads_id)->where('receiver_id', $receiver_id)->count()){
            Message::where('ads_jobs_id', $ads_id)->where('receiver_id', $receiver_id)->update([
                'active' => 1
            ]);
        }else{
            $message = new Message();
            if(App::getLocale() == 'en'){
                $message->text='Hi. I have chosen you for this job. I hope you are up to the responsibility';
                $content = 'You have been chosen to new project';
            }else{
                $message->text='السلام عليكم , لقد قمت باختيارك في هذه الوظيفة اتمنى ان تكون على قدر المسؤولية';
                $content = 'لقد تم اختيارك لهذا المشروع';
            }
            $message->sender()->associate(auth()->user());
            $message->receiver()->associate(User::find($receiver_id));
            $message->adsJob()->associate(AdsJobs::find($ads_id));
            $notificationConntroller = new NotificationController();
            // $content, $ads_id, $user_id
            $notificationConntroller->create($content, $ads_id, $receiver_id);
            $message->save();
            broadcast(new MessagesEvent($message));
        }
    }

    public function blockMessaegs($ads_id){
        Message::where('ads_jobs_id', $ads_id)->update([
            'active' => 0
        ]);
    }
    
    
    
    
    public function destroy(){
        $messages = Message::where('sender_id', auth()->user()->id)->get();
        foreach($messages as $msg){
            $c = Message::find($msg->id);
            $c->delete();
        }
        
        $messages = Message::where('receiver_id', auth()->user()->id)->get();
        foreach($messages as $msg){
            $c = Message::find($msg->id);
            $c->delete();
        }
    }
}
