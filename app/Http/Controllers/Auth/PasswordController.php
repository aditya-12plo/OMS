<?php

namespace App\Http\Controllers\Auth;

use DB;
use Mail;

use App\Models\User;
use App\Models\PasswordReset;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Http\Controllers\Controller as Controller;

class PasswordController extends Controller {

    public function __construct()
    {
      $this->frontUrl = "http://localhost:8080/reset-password/";
    }

    public function postEmail(Request $request) {
        $email 	= $request->input('email');
        $company_id = $request->input('company_id');
        
        $selectedUser = User::where([['email', '=', $email],["company_id", "=" , $company_id]])->with(['role','company'])->first();
        
        if ($selectedUser) {
            if($selectedUser->company->status === "ACTIVATE"){
              $token  = Str::uuid()->toString();
              $send   = ["link" => $this->frontUrl.$token];
              $content = view('emails.password')->with($send);
              Mail::send('layouts.email', ['contentMessage' => $content], function($message) use ($selectedUser) {
                  $message->to($selectedUser->email)->subject('User Reset Password');
                  $message->from('gojek.driver.sunter01@gmail.com','OMS Admin');
              });
               // check for failures
              if(Mail::failures()) {
                return response()
                ->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => "Mail send failed"]])
                ->withHeaders([
                  'Content-Type'          => 'application/json',
                  ])
                    ->setStatusCode(422);  
              }else{
                PasswordReset::insert(['company_id' => $selectedUser->company_id, 'email' => $selectedUser->email, 'token' => $token, 'created_at' => Carbon::now()]);
                return response()
                ->json(['status'=>200 ,'datas' => ['message' => "Please check your email."], 'errors' => []])
                ->withHeaders([
                  'Content-Type'          => 'application/json',
                  ])
                    ->setStatusCode(200);  
              }
            }else{
               return response()
               ->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => "company id not actived"]])
               ->withHeaders([
                 'Content-Type'          => 'application/json',
                 ])
                   ->setStatusCode(422);  
            }            
       } else {
           return response()
           ->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => "user not found"]])
           ->withHeaders([
             'Content-Type'          => 'application/json',
             ])
               ->setStatusCode(422);        
       }
    }


    public function postReset(Request $request, $token) {
      $this->validate($request, [
				'company_id' 		=> 'required|max:255|without_spaces', 
				'email' 		    => 'required|max:255|without_spaces', 
        'password' 		  => 'confirmed|max:255'
      ]);
      
      $checkToken = PasswordReset::where(['token' => $token , 'company_id' => $request->company_id , 'email' => $request->email])->first();
      if($checkToken){
        if(time() - strtotime($checkToken->created_at) < 3600) {
          User::where(['company_id' => $request->company_id , 'email' => $request->email])->update(['password' => Hash::make($request->password)]);
          return response()
          ->json(['status'=>200 ,'datas' => ['message' => "reset password successfully."], 'errors' => []])
          ->withHeaders([
            'Content-Type'          => 'application/json',
            ])
              ->setStatusCode(200); 
          } else {
            return response()
            ->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => "time limit has been expired"]])
            ->withHeaders([
              'Content-Type'          => 'application/json',
              ])
                ->setStatusCode(422); 
          } 
      }else{
        return response()
        ->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => "user not found"]])
        ->withHeaders([
          'Content-Type'          => 'application/json',
          ])
            ->setStatusCode(422); 
      }   
    }


}