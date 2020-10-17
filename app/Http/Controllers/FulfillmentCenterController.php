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
		
        $company_id     		= $request->company_id;
        $code     				= $request->code;
        $name     				= $request->name;
        $status     			= $request->status;
		
        if(!$sort_field){
            $sort_field = "fulfillment_center_id";
            $sort_type = "DESC";
        }
		
		if($auth->company_id == "OMS"){
			$query = FulfillmentCenter::with('company')->orderBy($sort_field,$sort_type);
		}else{
			$query = FulfillmentCenter::where('company_id', $auth->company_id)->with('company')->orderBy($sort_field,$sort_type);			
		}
		
		if ($company_id) {
            $like = "%{$company_id}%";
            $query = $query->where('company_id', 'LIKE', $like);
        }
		
		if ($code) {
            $like = "%{$code}%";
            $query = $query->where('code', 'LIKE', $like);
        }
		
		if ($name) {
            $like = "%{$name}%";
            $query = $query->where('name', 'LIKE', $like);
        }
		
		if ($status) {
            $query = $query->where('status',  $status);
        }
		
		return $query->paginate($perPage);
    }
	
	
    public function store(Request $request){
		
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
            'pic_faximile' 			=> 'max:12',  
            'country' 				=> 'required|max:255',  
            'pic_email' 			=> 'max:255|email',  
            'company' 				=> 'required|max:255',
            'pic_mobile' 			=> 'max:12'
        ]);
		
		$check	= FulfillmentCenter::where([["company_id",$request->company],["code",$request->fulfillment_code]])->first();
		if($check){
			
			return response()
				->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["fulfillment_code" => ["Fulfillment Code must be unique."]]]])
				->withHeaders([
				  'Content-Type'          => 'application/json',
				  ])
				->setStatusCode(422);
		}else{
		
			$send = array(
				'company_id' 	=> $request->company,
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
				'fax' 			=> $request->pic_faximile,
				'email' 		=> $request->pic_email,
				'status' 		=> $request->status
				); 
			FulfillmentCenter::create($send);
			
			
			return response()
				->json(['status'=>200 ,'datas' => ['message' => 'Add Successfully'], 'errors' => []])
				->withHeaders([
				  'Content-Type'          => 'application/json',
				  ])
				->setStatusCode(200);
			
		}
			
	}

	
    public function update(Request $request, $id){
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
				'pic_faximile' 			=> 'max:12',  
				'country' 				=> 'required|max:255',  
				'pic_email' 			=> 'max:255|email',  
				'company' 				=> 'required|max:255',
				'pic_mobile' 			=> 'max:12'
			]);
			
			$checkCode	= FulfillmentCenter::where([["company_id",$request->company],["code",$request->fulfillment_code]])->whereNotIn('fulfillment_center_id', [$id])->first();
			if($checkCode){
				return response()
					->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["fulfillment_code" => ["Fulfillment Code has been registered."]]]])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(422);
				
			}else{
				$send = array(
				'company_id' 	=> $request->company,
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
				'fax' 			=> $request->pic_faximile,
				'email' 		=> $request->pic_email,
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
	}

	
    public function updateStatus(Request $request, $id){
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
	}
	
	
}