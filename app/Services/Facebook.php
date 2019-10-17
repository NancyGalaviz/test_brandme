<?php

namespace App\Services;

use \Curl\Curl;
use App\Mail\GenerationToken;
use Illuminate\Support\Facades\Mail;
use App\Token;
use DateTime;

class Facebook {

    public function getRequest ($url,$data) {
        $response = null;
        try {
            $curl =  new Curl();		
            $curl->get($url,$data);
            if( $curl->error ){
                $error = $curl->errorMessage;
                \Log::info([$url,$data,json_encode($error)]);
            }else{
                $response = $curl->response;
            }
        } catch (\Throwable $th) {
            \Log::info($th);
            //throw $th;
        }
        return $response;
    }

    public function getUserData ($token) {
        $response = null;
        try {
            $data = array(
                'access_token'=>$token
            );
            $request = $this->getRequest("https://graph.facebook.com/me",$data);
            if (is_object($request)) {
                if (isset($request->name)) {
                    $response = [
                        "name" => $request->name,
                        "id" => $request->id
                    ];
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            \Log::info($th);
        }
        return $response;
    }

    public function oauthRequest ($type, $token = null) {
        $response = null;
        try {
            $data = array(
                "client_id" => (int) env('CLIENT', 371656837051056),
                "client_secret" => env('SECRET', '379e093b59ac642d0c217fc1d61ef2b4'),
                "grant_type" => $type
            );
            if (isset($token) && is_string($token)) {
                $data['fb_exchange_token'] = $token;
            }
            $response = $this->getRequest ("https://graph.facebook.com/v4.0/oauth/access_token",$data);
        } catch (\Throwable $th) {
            //throw $th;
            \Log::info($th);
        }
        return $response;
    }

    public function getAppToken () {
        $response = null;
        try {
            $request = $this->oauthRequest("client_credentials");
            if (is_object($request)){
                if (isset($request->access_token)) {
                    $response = $request->access_token;
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            \Log::info($th);
        }
        return $response;
    }

    public function debugToken ($token) {
        $response = null;
        try {
            $app_token = $this->getAppToken();
            $data = array(
                'input_token'=>$token,
                'access_token'=>$app_token
            );
            $request = $this->getRequest("https://graph.facebook.com/v4.0/debug_token",$data);
            if (is_object($request)) {
                if (isset($request->data)) {
                    $response = $request->data;
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            \Log::info($th);
        }
        return $response;
    }

    function isExpiredToken($token) {
        $response = null;
        try {
            $token = Token::where('user_token',$token)->first();
            $now = new DateTime();
            if ($now < $token->data_access_expires_at) {
                $response = false;
            } else {
                $response = true;
            }
        } catch (\Throwable $th) {
            //throw $th;
            \Log::info($th);
        }
        return $response;
    }

    public function refreshToken ($token, $email=null) {
        $response = null;
        try {
            $request = $this->oauthRequest("fb_exchange_token", $token);
            if (is_object($request)){
                if (isset($request->access_token)) {
                    $debug = $this->debugToken($request->access_token);
                    if (is_object($debug)) {
                        if (isset($debug->data_access_expires_at)){
                            $user = $this->getUserData($request->access_token);
                            if (is_null($email)) {
                                $email = [env('ADMIN_MAIL','nancygalaviz15@gmail.com')];
                            }
                            $response = [
                                "token" => $request->access_token,
                                "expire" => $debug->data_access_expires_at
                            ];
                            $expiration_date = new DateTime();
                            if (Token::where('user_id',$user['id'])->exists()){
                                $token = Token::where('user_id',$user['id'])->first();
                                $token->user_token = $response['token'];
                                $token->data_access_expires_at = $expiration_date->setTimestamp($response['expire']);
                                $token->save();
                            }else{
                                $token = new Token;
                                $token->user_id = $user['id'];
                                $token->user_token = $response['token'];
                                $token->data_access_expires_at = $expiration_date->setTimestamp($response['expire']);
                                $token->save();
                            }
                            \Log::info('emails to send',$email);
                            Mail::to($email)->send(new GenerationToken($user));
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            \Log::info($th);
        }
        return $response;
    }

}
