<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubCompanyRequest;
use App\Models\Company;
use App\Models\SubCompany;
use Illuminate\Http\Request;

class SubCompanyController extends BaseController
{

    public function store(SubCompanyRequest $request)
    {
        $subCompany = new SubCompany($request->all());
        $subCompany->user()->associate(auth()->user())->save();
        $subCompany->company()->associate(Company::find($request->company_id))->save();
        return $this->sendResponse($subCompany, __('messages.added_success'));

    }

    public function show($id)
    {
        //
    }





    public function update(SubCompanyRequest $request, $id)
    {
        $subCompany = SubCompany::find($id);
        $subCompany->update($request->all());
        return $this->sendResponse($subCompany, __('messages.edited_success'));
    }
    
    public function destroyByUser($arr){
        foreach($arr as $elm){
            $this->destroy($elm->id);
        }
    }

    public function destroy($id)
    {
        $subCompany = SubCompany::find($id);
        $subCompany->delete();
        return $this->sendResponse('', __('messages.deleted_data'));

    }
}
