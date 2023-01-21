<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExperienceRequest;
use App\Models\Experience;
use Illuminate\Http\Request;

class ExperienceController extends BaseController
{

    public function index()
    {
        //
    }



    public function store(ExperienceRequest $request)
    {
        $experience = new Experience($request->all());
        $experience->user()->associate(auth()->user())->save();
        return $this->sendResponse($experience, __('messages.added_success'));

    }


    public function show($id)
    {
        return Experience::find($id);
    }



    public function update(ExperienceRequest $request, $id)
    {
        $experience = $this->show($id);
        $experience->update($request->all());
        return $this->sendResponse($experience, __('messages.edited_success'));
    }
    
    public function destroyByUser($arr){
        foreach($arr as $elm){
            $this->destroy($elm->id);
        }
    }


    public function destroy($id)
    {
        $experience = Experience:: find($id);
        $experience->delete();
        return $this->sendResponse('', __('messages.deleted_data'));
    }
}
