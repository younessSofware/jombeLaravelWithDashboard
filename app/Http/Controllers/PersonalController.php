<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonalInfoRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Storage;

class PersonalController extends BaseController
{

    public function index()
    {
        //
    }

    public function updateProfile(PersonalInfoRequest $request)
    {
        $user = User::find(auth()->user()->id);
        if($request->hasFile('photo')){
            $file      = $request->file('photo');
            $contents = file_get_contents($file);
            $filename  = $user->fullname."-".Carbon::now();
            $storage_disk = 'public';
            Storage::disk($storage_disk)->put('uploads/'.$filename.'.png', $contents);
            $user->photo = 'storage/uploads/'.$filename.'.png';
            // // $file->move(public_path('storage/storage/uploads'), $filename);
            // $user->photo = 'storage/storage/uploads/'.$filename;
        }
        $user->country = $request->country;
        $user->phone = $request->phone;
        $user->gender = $request->gender;
        $user->dob = $request->dob;
        $user->save();
        return $this->sendResponse($user, __('messages.added_success'));
    }

    public function show($id)
    {
        //
    }

}
