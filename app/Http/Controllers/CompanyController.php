<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Storage;
use Carbon\Carbon;
class CompanyController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        
        if($req->user_id){
            $user_id = $req->user_id;
        }else{
            $user_id = auth()->user()->id;
        }
        $company = Company::where('user_id', $user_id)->first();
        $data = (object)[];
        if($company){
            $data = $company;
            $data->subCompany = $company->subcompanies()->get();
        }
        $data->user = User::where("id", $user_id)->with("adsJobs")->first();
        $data->name = auth()->user()->fullname;
        $data->photo = auth()->user()->photo;
        $data->email = auth()->user()->email;
        return $this->sendResponse($data, __('messages.found_result'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CompanyRequest $request)
    {
        $user = User::find(auth()->user()->id);
        $last_email = $user->email;
        $user->update($request->all());
        if($request->hasFile('photo')){
            $file      = $request->file('photo');
            $contents = file_get_contents($file);
            $filename  = $user->fullname."-".Carbon::now();
            $storage_disk = 'public';
            Storage::disk($storage_disk)->put('uploads/'.$filename.'.png', $contents);
            $user->photo = 'storage/uploads/'.$filename.'.png';
            $user->save();
        }
        $company = Company::where('user_id',auth()->user()->id)->first();
        if($company){
            $company->update($request->all());
        }else{
            $request->user_id = $user->id;
            $company = new Company($request->all());
            $company->user()->associate($user);
            $company->save();
        }
        $user->email = $last_email;
        $user->save();
        $company->user = $user;
        return $this->sendResponse($company, __('messages.added_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function destroy()
    {
        $company = Company::where('user_id', auth()->user()->id)->first();
        if($company)
        $company->delete();
    }
}
