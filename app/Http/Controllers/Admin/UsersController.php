<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistreRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        $searchQuery = urldecode($req->searchQuery);
        
        $searchQuery = json_decode($searchQuery) ;
        if($searchQuery->id == ""){
            $users = User::
            where('email',"like","%$searchQuery->email%")
            ->where('fullname',"like","%$searchQuery->fullname%")
            ->where("username","like","%$searchQuery->username%")
            ->where("role", $searchQuery->role)->skip($req->skip)->take($req->take)->get();
            $total = User::
            where('email',"like","%$searchQuery->email%")
            ->where('fullname',"like","%$searchQuery->fullname%")
            ->where("username","like","%$searchQuery->username%")
            ->where("role", $searchQuery->role)->count();
        }else{
            $users = User::
            where('email',"like","%$searchQuery->email%")
            ->where('fullname',"like","%$searchQuery->fullname%")
            ->where("username","like","%$searchQuery->username%")
            ->where("id", "$searchQuery->id")
            ->where("role", $searchQuery->role)->skip($req->skip)->take($req->take)->get();
            $total = User::
            where('email',"like","%$searchQuery->email%")
            ->where('fullname',"like","%$searchQuery->fullname%")
            ->where("username","like","%$searchQuery->username%")
            ->where("id", "$searchQuery->id")
            ->where("role", $searchQuery->role)->count();
        }
        $data = ["total" => $total, "data" => $users];
        return $this->sendResponse($data, __('messages.found_result'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function activeUsers(Request $req)
    {
        $ids = $req->ids;
        foreach ($ids as $id ) {
            $user = User::find($id);
            if($user->active == 0) $user->active = 1;
            else if($user->active == 1) $user->active = 0;
            $user->save();
        }

        return $this->sendResponse('', __('messages.save_data'));

    }






    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegistreRequest $request)
    {
                // return $request->all();
                $user = new User($request->all());

                $user->username = $request->username;
                $user->password = bcrypt($request->password);
                if(auth()->user() && auth()->user()->role == 1){
                    $user->createdByAdmin = 1;
                }
                $user->save();
                if($user->role == 3){
                    $company = new Company();
                    $company->user()->associate($user)->save();
                }


                return $this->sendResponse($user, __('messages.register_success'));
    }


    public function show($id)
    {
        return $this->sendResponse(User::find($id), __('messages.found_result'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $req)
    {
        $user = User::find($req->id);
        $user->update($req->all());
        return $this->sendResponse($user, __('messages.edited_success'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $auth = new AuthController();
        return $auth->removeAccount($id);
    }


    public function findByUsername(Request $req){
        $user = User::where("username", $req->username)->first();
        return $this->sendResponse($user, __('messages.found_result'));
    }
}
