<?php

namespace App\Http\Controllers\Auth;

use DB;

use App\Models\User;
use App\Models\UserRole;

use Firebase\JWT\JWT;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Laravel\Lumen\Routing\Controller as BaseController;

class LoginController extends BaseController {
    
    private function jwt(User $user) {
        
        $payload = [
            'iss' => "bearer",
            'sub' => $user,
            'iat' => time(),
            'exp' => time() + 1440*60 // token kadaluwarsa setelah 3600 detik
        ];
        
        return JWT::encode($payload, env('APP_KEY'));
    
    }
    
    public function authenticate(Request $request) {
        
        $email 	= $request->input('email');
    	$password 	= $request->input('password');
    	$company_id = $request->input('company_id');
        
		
		$selectedUser = User::where([['email', '=', $email],["company_id", "=" , $company_id]])->with(['role','company'])->first();
        
        if ($selectedUser && Hash::check($password, $selectedUser->password)) {
             if($selectedUser->company->status === "ACTIVATE"){
				$token = $this->jwt($selectedUser);
            
				$data = ['token' => $token, 'type' => 'bearer'];
				return response()
				->json(['status'=>200 ,'datas' => $data, 'errors' => []])
				->withHeaders([
				  'Content-Type'          => 'application/json',
				  ])
					->setStatusCode(200);
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
			->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => "user not found / company id not actived"]])
			->withHeaders([
			  'Content-Type'          => 'application/json',
			  ])
				->setStatusCode(422);        
        }

    }

}