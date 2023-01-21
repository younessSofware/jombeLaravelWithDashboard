<?php

namespace App\Http\Controllers;

use App\Http\Requests\CertificateRequest;
use App\Http\Requests\SkillRequest;
use App\Models\Certificate;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends BaseController
{


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SkillRequest $request)
    {
        $skill = new Skill($request->all());
        $skill->user()->associate(auth()->user())->save();
        return $this->sendResponse($skill, __('messages.added_success'));

    }


    public function show($id)
    {
        return Skill::find($id);
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
        $skill = $this->show($id);
        $skill->update($request->all());
        return $this->sendResponse($skill, __('messages.edited_success'));

    }
    
    public function destroyByUser($arr){
        foreach($arr as $elm){
            $this->destroy($elm->id);
        }
    }


    public function destroy($id)
    {
        $skill = Skill:: find($id);
        $skill->delete();
        return $this->sendResponse('', __('messages.deleted_data'));

    }
}
