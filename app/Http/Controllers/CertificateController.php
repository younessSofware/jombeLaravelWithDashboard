<?php

namespace App\Http\Controllers;

use App\Http\Requests\CertificateRequest;
use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateController extends BaseController
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


    public function store(CertificateRequest $request)
    {
        $certificate = new Certificate($request->all());
        $certificate->user()->associate(auth()->user())->save();
        return $this->sendResponse($certificate, __('messages.saved_data'));

    }


    public function show($id)
    {
        return Certificate::find($id);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CertificateRequest $request, $id)
    {
        $certificate = $this->show($id);
        $certificate->update($request->all());
        $response = [
            'Result' => [
                'certificate' => $certificate,
            ],
        ];
        return $this->sendResponse($certificate, __('messages.edited_success'));
    }
    
    public function destroyByUser($arr){
        foreach($arr as $elm){
            $this->destroy($elm->id);
        }
    }


    public function destroy($id)
    {
        $certificate = Certificate:: find($id);
        $certificate->delete();
        return $this->sendResponse('', __('messages.deleted_data'));

    }
}
