<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'StatusCode' => 200,
            'Status' => true,
            'Result'    => $result,
            'Message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'StatusCode' => -1,
            'Status' => false,
            'Message' => $error,
        ];

        if(!empty($errorMessages)){
            $response = $errorMessages;
        }

        return response()->json($response, $code);
    }


}
