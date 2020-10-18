<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Log;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Models\User;
use App\Models\Products;
use App\Models\FulfillmentCenter;
use App\Models\Inventory;

class ProductsController extends Controller
{
	public function __construct(){
		$this->middleware('jwt.auth');
    }
	
	
    public function normalProducts(Request $request){
		$auth					= $request->auth;
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $company_id     		= $request->company_id;
        $product_description	= $request->product_description;
        $product_code			= $request->product_code;
        $price					= $request->price;
		
        if(!$sort_field){
            $sort_field = "product_id";
            $sort_type = "DESC";
        }
		
		if($auth->company_id == "OMS"){
			$query = Products::with(['company','uom_description','inventory.fulfillment'])->orderBy($sort_field,$sort_type);
		
		
			if ($company_id) {
				$like = "%{$company_id}%";
				$query = $query->where('company_id', 'LIKE', $like);
			}
					
			if ($product_code) {
				$like = "%{$product_code}%";
				$query = $query->where('product_code', 'LIKE', $like);
			}
					
			if ($product_description) {
				$like = "%{$product_description}%";
				$query = $query->where('product_description', 'LIKE', $like);
			}
			
					
			if ($price) {
				$query = $query->whereRaw('price '.$price);
			}
			
		}else{
			$query = Products::with(['uom_description','inventory'])->where('company_id', $auth->company_id)->orderBy($sort_field,$sort_type);
							
			if ($product_code) {
				$like = "%{$product_code}%";
				$query = $query->where('product_code', 'LIKE', $like);
			}
					
			if ($product_description) {
				$like = "%{$product_description}%";
				$query = $query->where('product_description', 'LIKE', $like);
			}
					
			if ($price) {
				$query = $query->whereRaw('price '.$price);
			}
		}
		
		return $query->paginate($perPage);
    }
	
	
	
    public function normalAddProducts(Request $request){
		$auth	= $request->auth;
		if($auth->company_id == "OMS" || $auth->company_id == $request->company){
			$this->validate($request, [
					'product_code' 			=> 'required|max:255|without_spaces', 
					'company' 				=> 'required|max:255',
					'product_name' 			=> 'required|max:255',  
					'uom_code' 				=> 'required|max:255',
					'price' 				=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'width' 				=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'height' 				=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'weight' 				=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'net_weight'			=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'gross_weight'			=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'qty_per_carton'		=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'carton_per_pallet'		=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'cube'					=> "required|regex:/^\d*(\.\d{1,2})?$/", 
					'currency' 				=> 'required|max:255',
					'barcode' 				=> 'max:255',
					'time_to_live' 			=> 'required|in:0,1'
				]);
				
			$checkCode	= Products::where([["company_id",$request->company],["product_code",$request->product_code]])->first();
			if($checkCode){
					return response()
						->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["product_code" => ["Product Code has been registered."]]]])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(422);
					
			}else{
				$data 						= new Products;
				$data->product_code 		= $request->product_code;
				$data->company_id 			= $request->company;
				$data->product_description 	= $request->product_name;
				$data->uom_code 			= $request->uom_code;
				$data->price 				= $request->price;
				$data->width 				= $request->width;
				$data->height 				= $request->height;
				$data->weight 				= $request->weight;
				$data->net_weight			= $request->net_weight;
				$data->gross_weight			= $request->gross_weight;
				$data->qty_per_carton		= $request->qty_per_carton;
				$data->carton_per_pallet	= $request->carton_per_pallet;
				$data->cube					= $request->cube;
				$data->currency				= $request->currency;
				$data->barcode				= $request->barcode;
				$data->time_to_live			= $request->time_to_live;
				$data->type					= 'normal';
				$data->save();
				
				$productId	= $data->product_id;
				
				$fulfillments = FulfillmentCenter::where('company_id', $data->company_id)->get();
				if(count($fulfillments) > 0){
					foreach($fulfillments as $fulfillment){
						$inventory								= new Inventory;
						$inventory->product_id	 				= $productId;
						$inventory->fulfillment_center_id	 	= $fulfillment->fulfillment_center_id;
						$inventory->company_id	 				= $request->company;
						$inventory->stock_on_hand 				= 0;
						$inventory->stock_available				= 0;
						$inventory->stock_hold	 				= 0;
						$inventory->stock_booked 				= 0;
						$inventory->save();
						
					}
				}
				
				
				return response()
					->json(['status'=>200 ,'datas' => ['message' => 'Add Successfully'], 'errors' => []])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(200);
			}
		}else{
			return response()
					->json(['status'=>400 ,'datas' => [], 'errors' => ['product_code' => 'Data not available']])
					->withHeaders([
						'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(400);
			
		}
	}
	
	
    public function normalUpdateProducts(Request $request, $id){
		$auth	= $request->auth;
		$cek 	= Products::findOrFail($id);
		
		if(!$cek){
			return response()
					->json(['status'=>400 ,'datas' => [], 'errors' => ['product_code' => 'Data not available']])
					->withHeaders([
						'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(400);
		}elseif($cek->type == 'BUNDLE'){
			return response()
					->json(['status'=>400 ,'datas' => [], 'errors' => ['product_code' => 'Data not available']])
					->withHeaders([
						'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(400);
			
		}else{
			if($auth->company_id == "OMS" || $auth->company_id == $request->company){
				$this->validate($request, [
					'product_code' 			=> 'required|max:255|without_spaces', 
					'company' 				=> 'required|max:255',
					'product_name' 			=> 'required|max:255',  
					'uom_code' 				=> 'required|max:255',
					'price' 				=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'width' 				=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'height' 				=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'weight' 				=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'net_weight'			=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'gross_weight'			=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'qty_per_carton'		=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'carton_per_pallet'		=> "required|regex:/^\d*(\.\d{1,2})?$/",
					'cube'					=> "required|regex:/^\d*(\.\d{1,2})?$/", 
					'currency' 				=> 'required|max:255',
					'barcode' 				=> 'max:255',
					'time_to_live' 			=> 'required|in:0,1'
				]);
				
				$checkCode	= Products::where([["company_id",$request->company],["product_code",$request->product_code]])->whereNotIn('product_id', [$id])->first();
				if($checkCode){
					return response()
						->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["product_code" => ["Product Code has been registered."]]]])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(422);
					
				}else{
					$send = array(
						'product_code'			=> $request->product_code,
						'company_id' 			=> $request->company,
						'product_description' 	=> $request->product_name,
						'uom_code' 				=> $request->uom_code,
						'price' 				=> $request->price,
						'width' 				=> $request->width,
						'height' 				=> $request->height,
						'weight' 				=> $request->weight,
						'net_weight' 			=> $request->net_weight,
						'gross_weight' 			=> $request->gross_weight,
						'qty_per_carton' 		=> $request->qty_per_carton,
						'carton_per_pallet'		=> $request->carton_per_pallet,
						'cube' 					=> $request->cube,
						'currency' 				=> $request->currency,
						'barcode' 				=> $request->barcode,
						'time_to_live' 			=> $request->time_to_live
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
						->json(['status'=>400 ,'datas' => [], 'errors' => ['product_code' => 'Data not available']])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(400);
				
			}
		}		
	}
	 
}