<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use View,Input,Session,File,Hash,DB,Mail;
use Illuminate\Support\Facades\Crypt;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Illuminate\Support\Facades\Validator;
use Log;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Models\User;
use App\Models\FulfillmentCenter;

class FulfillmentCenterController extends Controller
{
	public function __construct(){
		$this->middleware('jwt.auth');
    }

    public function index(Request $request){
		$auth					= $request->auth;
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $code     				= $request->code;
        $name     				= $request->name;
        $fulfillment_center_type_id	= $request->fulfillment_center_type_id;
        $status     			= $request->status;
		
        if(!$sort_field){
            $sort_field = "fulfillment_center_id";
            $sort_type = "DESC";
        }
		
			$query = FulfillmentCenter::orderBy($sort_field,$sort_type);
			
			
			if ($code) {
				$like = "%{$code}%";
				$query = $query->where('code', 'LIKE', $like);
			}
			
			if ($name) {
				$like = "%{$name}%";
				$query = $query->where('name', 'LIKE', $like);
			}
			
			if ($fulfillment_center_type_id) {
				$like = "%{$fulfillment_center_type_id}%";
				$query = $query->where('fulfillment_center_type_id', 'LIKE', $like);
			}
			
			if ($status) {
				$query = $query->where('status',  $status);
			}
			
			return $query->paginate($perPage);
		
    }
	
	
    public function detail(Request $request, $id){
		$auth	= $request->auth;
		$query = FulfillmentCenter::where("fulfillment_center_id",$id)->first();
		if($query){
			return response()
					->json(['status'=>200 ,'datas' => $query, 'errors' => []])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(200);
		}else{
			return response()
						->json(['status'=>404 ,'datas' => [], 'errors' => ['message' => ["product_code" => ["Fulfillment Not Found."]]]])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(404);
			
		}
	}
	
	
    public function store(Request $request){
		$auth					= $request->auth;
		if($auth->company_id == "OMS" && $auth->user_role_id == "ADMIN"){
			$this->validate($request, [
				'fulfillment_code' 		=> 'required|max:255|without_spaces', 
				'fulfillment_name' 		=> 'required|max:255',  
				'address' 				=> 'required',  
				'province' 				=> 'required|max:255',  
				'city' 					=> 'required|max:255',  
				'area' 					=> 'max:255',  
				'sub_area' 				=> 'max:255',  
				'village' 				=> 'max:255',  
				'postal_code' 			=> 'required|max:6|without_spaces', 
				'longitude' 			=> 'max:255',  
				'latitude' 				=> 'max:255',  
				'fulfillment_center_type'	=> 'max:255',  
				'status' 				=> 'required|in:ACTIVATE,DEACTIVATE',  
				'pic_name' 				=> 'required|max:255',  
				'pic_phone' 			=> 'max:10',  
				'pic_fax' 				=> 'max:12',  
				'country' 				=> 'required|max:255',  
				'pic_email' 			=> 'max:255|email',
				'pic_mobile' 			=> 'max:12'
			]);
			
			$check	= FulfillmentCenter::where("code",$request->fulfillment_code)->first();
			if($check){
				
				return response()
					->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["fulfillment_code" => ["Fulfillment Code must be unique."]]]])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(422);
			}else{
					
				$data 						= new FulfillmentCenter;
				$data->code 				= $request->fulfillment_code;
				$data->name 				= $request->fulfillment_name;
				$data->address	 			= $request->address;
				$data->address2 			= $request->address2;
				$data->province 			= $request->province;
				$data->city				 	= $request->city;
				$data->area 				= $request->area;
				$data->sub_area				= $request->sub_area;
				$data->village 				= $request->village;
				$data->postal_code			= $request->postal_code;
				$data->country 				= $request->country;
				$data->latitude				= $request->latitude;
				$data->longitude			= $request->longitude;
				$data->remarks				= $request->remarks;
				$data->pic					= $request->pic_name;
				$data->phone				= $request->pic_phone;
				$data->mobile				= $request->pic_mobile;
				$data->fax					= $request->pic_fax;
				$data->email				= $request->pic_email;
				$data->fulfillment_center_type_id	= $request->fulfillment_center_type;
				$data->status				= $request->status;
				$data->save();
				
				return response()
					->json(['status'=>200 ,'datas' => ['message' => 'Add Successfully', 'datas' => $data], 'errors' => []])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(200);
				
			}
		}else{
			
			return response()
				->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["fulfillment_code" => ["No have Access."]]]])
				->withHeaders([
				  'Content-Type'          => 'application/json',
				  ])
				->setStatusCode(422);
		}
			
	}

	
    public function update(Request $request, $id){
		$auth					= $request->auth;
		if($auth->company_id == "OMS" && $auth->user_role_id == "ADMIN"){
			$auth	= $request->auth;
			$cek 	= FulfillmentCenter::findOrFail($id);
			
			if(!$cek){
				
				return response()
						->json(['status'=>400 ,'datas' => [], 'errors' => ['fulfillment_center_id' => 'Data not available']])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(400);
					
			}else{
				$this->validate($request, [
					'fulfillment_code' 		=> 'required|max:255|without_spaces', 
					'fulfillment_name' 		=> 'required|max:255',  
					'address' 				=> 'required',  
					'province' 				=> 'required|max:255',  
					'city' 					=> 'required|max:255',  
					'area' 					=> 'max:255',  
					'sub_area' 				=> 'max:255',  
					'village' 				=> 'max:255',  
					'postal_code' 			=> 'required|max:6|without_spaces', 
					'longitude' 			=> 'max:255',  
					'latitude' 				=> 'max:255',  
					'status' 				=> 'required|in:ACTIVATE,DEACTIVATE',  
					'pic_name' 				=> 'required|max:255',  
					'pic_phone' 			=> 'max:10',  
					'pic_fax' 				=> 'max:12',  
					'country' 				=> 'required|max:255',  
					'fulfillment_center_type'=> 'required|max:255',  
					'pic_email' 			=> 'max:255|email',  
					'pic_mobile' 			=> 'max:12'
				]);
				
				$checkCode	= FulfillmentCenter::where("code",$request->fulfillment_code)->whereNotIn('fulfillment_center_id', [$id])->first();
				if($checkCode){
					return response()
						->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["fulfillment_code" => ["Fulfillment Code has been registered."]]]])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(422);
					
				}else{
					$send = array(
					'code' 			=> $request->fulfillment_code,
					'name' 			=> $request->fulfillment_name,
					'address' 		=> $request->address,
					'address2' 		=> $request->address2,
					'province' 		=> $request->province,
					'city' 			=> $request->city,
					'area' 			=> $request->area,
					'sub_area' 		=> $request->sub_area,
					'village' 		=> $request->village,
					'postal_code' 	=> $request->postal_code,
					'country' 		=> $request->country,
					'latitude' 		=> $request->latitude,
					'longitude' 	=> $request->longitude,
					'remarks' 		=> $request->remarks,
					'pic' 			=> $request->pic_name,
					'phone' 		=> $request->pic_phone,
					'mobile' 		=> $request->pic_mobile,
					'fax' 			=> $request->pic_fax,
					'email' 		=> $request->pic_email,
					'fulfillment_center_type_id' 		=> $request->fulfillment_center_type,
					'status' 		=> $request->status
					); 
					
					$cek->update($send);
				
					return response()
						->json(['status'=>200 ,'datas' => ['message' => 'Update Successfully'], 'errors' => []])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(200);
				}
			}
		}else{
			return response()
						->json(['status'=>404 ,'datas' => [], 'errors' => ['message' => ["product_code" => ["Fulfillment Not Found."]]]])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(404);
			
		}		
	}

	
    public function updateStatus(Request $request, $id){
		$auth	= $request->auth;
			if($auth->company_id == "OMS" && $auth->user_role_id == "ADMIN"){
			$cek 	= FulfillmentCenter::findOrFail($id);
			
			if(!$cek){
				
				return response()
						->json(['status'=>400 ,'datas' => [], 'errors' => ['fulfillment_center_id' => 'Data not available']])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(400);
					
			}else{
				$this->validate($request, [
					'status' 				=> 'required|in:ACTIVATE,DEACTIVATE'
				]);
				
				$send = array(
					'status' 		=> $request->status
					); 
					
				$cek->update($send);
				
				return response()
						->json(['status'=>200 ,'datas' => ['message' => 'Update Successfully'], 'errors' => []])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(200);
			}
		}else{
			return response()
						->json(['status'=>404 ,'datas' => [], 'errors' => ['message' => ["product_code" => ["Fulfillment Not Found."]]]])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(404);
			
		}				
	}
	
	
}