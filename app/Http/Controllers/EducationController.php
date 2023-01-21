<?php

namespace App\Http\Controllers;

use App\Http\Requests\EducationRequest;
use App\Models\Education;
use Illuminate\Http\Request;

class EducationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    public function store(EducationRequest $request)
    {
        $education = new Education($request->all());
        $education->user()->associate(auth()->user())->save();
        return $this->sendResponse($education, __('messages.added_success'));
    }


    public function show($id)
    {
        return Education::find($id);
    }

    public function update(EducationRequest $request, $id)
    {
        $education = $this->show($id);
        $education->update($request->all());
        return $this->sendResponse($education, __('messages.edited_success'));
    }
    
    public function destroyByUser($arr){
        foreach($arr as $elm){
            $this->destroy($elm->id);
        }
    }

    public function destroy($id)
    {
        $education = Education:: find($id);
        $education->delete();
        return $this->sendResponse('', __('messages.deleted_data'));

    }
}
