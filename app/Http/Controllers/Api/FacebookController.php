<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Curl\Curl;
use App\Services\Facebook;
use App\Token;

class FacebookController extends Controller
{
    public function saveToken(Request $request){
        $response = null;
        try {
        $token = $request->access_token;
        $facebook = new Facebook(); 
        $response = $facebook->refreshToken($token);
        } catch (\Throwable $th) {
            \Log::info($th);
        }
        return response()->json([$response]);
    }

    public function verifyToken($token, Request $request){
        $response = null;
        try {
        $facebook = new Facebook();
        $response = $facebook->isExpiredToken($token);
        } catch (\Throwable $th) {
            \Log::info($th);
        }
        return response()->json(["response"=>$response]);
    }
}
