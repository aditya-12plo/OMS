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
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use App\Models\User;
use App\Models\ProductsDamage;
use App\Models\ProductsKit;
use App\Models\FulfillmentCenter;
use App\Models\CompanyFulfillment;
use App\Models\Inventory;
use App\Models\Products;
use App\Models\LocationsDamageDetail;

class ProductsDamageController extends Controller
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
        $product_description	= $request->product_description;
        $product_code			= $request->product_code;
        $status					= $request->status;
		
        if(!$sort_field){
            $sort_field = "products_demage_id";
            $sort_type = "DESC";
        }
		
		if($auth->company_id == "OMS"){
			$query = ProductsDamage::with(['product','fulfillment','locationDamage.locationDescription'])->orderBy($sort_field,$sort_type);
		
			
		}else{
			$query = ProductsDamage::with(['product','fulfillment','locationDamage.locationDescription'])->where('company_id', $auth->company_id)->orderBy($sort_field,$sort_type);
				
		}
		
			
		
			if ($company_id) {
				$like = "%{$company_id}%";
				$query = $query->whereHas('product', function($query) use ($like){
					$query->where('company_id', 'LIKE', $like);
				});
			}
					
			if ($product_code) {
				$like = "%{$product_code}%";
				$query = $query->whereHas('product', function($query) use ($like){
					$query->where('product_code', 'LIKE', $like);
				});
			}
					
			if ($product_description) {
				$like = "%{$product_description}%";
				$query = $query->whereHas('product', function($query) use ($like){
					$query->where('product_description', 'LIKE', $like);
				});
			}
			
					
			if ($status) {
				$query = $query->where('status',$status);
			}
		
		return $query->paginate($perPage);
    }


	
	public function updateStatus(Request $request){
		$auth					= $request->auth;
		$this->validate($request, [
            'products_demage_id'	=> 'required|integer|min:0|not_in:0',
            'status'				=> 'required|in:HOLD,SALE',
        ]);
		
		$checkDamage	= ProductsDamage::with(['product','fulfillment','locationDamage.locationDescription'])->where("products_demage_id" , $request->products_demage_id)->first();
		if($checkDamage){
			if($request->status	== "HOLD" && $checkDamage->status == "SALE"){
				$checkDamage->sale_date	= null;
				$checkDamage->sale_by	= null;
				$checkDamage->hold_by	= $auth->name.' - '.$auth->company_id;
				$checkDamage->hold_date	= date('Y-m-d');
				$checkDamage->status	= $request->status;
				$checkDamage->save();
				
				$inventory	= Inventory::where([["product_id",$checkDamage->product_id],["fulfillment_center_id",$checkDamage->fulfillment_center_id],["company_id",$checkDamage->product->company_id]])->first();
				if($inventory){
					$stock_hold			= $inventory->stock_hold + $checkDamage->qty;
					$stock_available	= $inventory->stock_available - $checkDamage->qty;
						
					$inventory->stock_hold		= $stock_hold;
					$inventory->stock_available	= $stock_available;
					$inventory->save();
				}
				
				
				return response()
					->json(['status'=>200 ,'datas' => ['message' => 'Update Successfully'], 'errors' => []])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(200);
			}elseif($request->status == "SALE" && $checkDamage->status == "HOLD"){
				
				$checkDamage->sale_by	= $auth->name.' - '.$auth->company_id;
				$checkDamage->sale_date	= date('Y-m-d');
				$checkDamage->status	= $request->status;
				$checkDamage->save();
				
				$inventory	= Inventory::where([["product_id",$checkDamage->product_id],["fulfillment_center_id",$checkDamage->fulfillment_center_id],["company_id",$checkDamage->product->company_id]])->first();
				if($inventory){
					$stock_hold			= $inventory->stock_hold - $checkDamage->qty;
					$stock_available	= $inventory->stock_available + $checkDamage->qty;
						
					$inventory->stock_hold		= $stock_hold;
					$inventory->stock_available	= $stock_available;
					$inventory->save();
				}
				
				
				return response()
					->json(['status'=>200 ,'datas' => ['message' => 'Update Successfully'], 'errors' => []])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(200);
			}else{
				return response()
						->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["status" => ["status not register"]]]])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(422);
			}	
		}else{
			return response()
					->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["status" => ["status not register"]]]])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(422);
		}
	}
	
	
	public function store(Request $request){
		$auth					= $request->auth;
		
		$this->validate($request, [
            'company' 				=> 'required|max:255|without_spaces',
            'fulfillment_center_id'	=> 'required|integer|min:0|not_in:0',
            'location_id'			=> 'required|integer|min:0|not_in:0',
            'product_code'			=> 'required|integer|min:0|not_in:0',
            'qty'					=> 'required|integer|min:0|not_in:0',
            'reason'				=> 'required',
        ]);
		$check	= CompanyFulfillment::where([['company_id',$request->company],['fulfillment_center_id',$request->fulfillment_center_id]])->first();
		
		if($check->company_id == $auth->company_id || $auth->company_id == "OMS"){
			$checkDamage	= ProductsDamage::where([["product_id", $request->product_code], ["fulfillment_center_id", $request->fulfillment_center_id], ["status","HOLD"]])->first();
			if(!$checkDamage){
			
				$productDamage							= new ProductsDamage;
				$productDamage->fulfillment_center_id	= $request->fulfillment_center_id;
				$productDamage->product_id				= $request->product_code;
				$productDamage->qty						= $request->qty;
				$productDamage->reason					= $request->reason;
				$productDamage->additional_reason		= $request->additional_reason;
				$productDamage->hold_by					= $auth->name.' - '.$auth->company_id;
				$productDamage->hold_date				= date('Y-m-d');
				$productDamage->status					= 'HOLD';
				$productDamage->save();
				
				$productDamageId	= $productDamage->products_demage_id;
				
				$productDamageLocation						= new LocationsDamageDetail;
				$productDamageLocation->location_id			= $request->location_id;
				$productDamageLocation->products_demage_id	= $productDamageId;
				$productDamageLocation->qty					= $request->qty;
				$productDamageLocation->save();
				
				$inventory	= Inventory::where([["product_id",$request->product_code],["fulfillment_center_id",$request->fulfillment_center_id],["company_id",$request->company]])->first();
				if($inventory){
					$stock_hold			= $inventory->stock_hold + $request->qty;
					$stock_available	= $inventory->stock_available - $request->qty;
					
					$inventory->stock_hold		= $stock_hold;
					$inventory->stock_available	= $stock_available;
					$inventory->save();
				}
				
				
				return response()
					->json(['status'=>200 ,'datas' => ['message' => 'Add Successfully'], 'errors' => []])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(200);
				
			}else{
				return response()
					->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["product_code" => ["product code has been register"]]]])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(422);
				
			}
			
		}else{
			return response()
					->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["company" => ["company not has register"]]]])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(422);
			
		}
	}

    public function detail(Request $request, $id){
		$auth	= $request->auth;
		$query = ProductsDamage::with(['product','fulfillment','locationDamage.locationDescription'])->where("products_demage_id",$id)->first();
		if($query){
			if($auth->company_id == "OMS" || $auth->company_id == $request->company){
				return response()
					->json(['status'=>200 ,'datas' => $query, 'errors' => []])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(200);
			}else{
				return response()
							->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["product_code" => ["Product Id not registered."]]]])
							->withHeaders([
							  'Content-Type'          => 'application/json',
							  ])
							->setStatusCode(422);
				
			}
		}else{
			return response()
						->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["product_code" => ["Product Id not registered."]]]])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(422);
			
		}
	}
	
	
    public function update(Request $request, $id){
		$auth					= $request->auth;
		
		$this->validate($request, [
            'company' 				=> 'required|max:255|without_spaces',
            'fulfillment_center_id'	=> 'required|integer|min:0|not_in:0',
            'location_id'			=> 'required|integer|min:0|not_in:0',
            'product_code'			=> 'required|integer|min:0|not_in:0',
            'qty'					=> 'required|integer|min:0|not_in:0',
            'reason'				=> 'required',
        ]);
		
		$check	= CompanyFulfillment::where([['company_id',$request->company],['fulfillment_center_id',$request->fulfillment_center_id]])->first();
		
		if($check->company_id == $auth->company_id || $auth->company_id == "OMS"){
			$checkDamage	= ProductsDamage::where([["product_id", $request->product_code], ["fulfillment_center_id", $request->fulfillment_center_id],["status","HOLD"]])->whereNotIn('products_demage_id', [$id])->first();
			if(!$checkDamage){
				$oldDatas	= ProductsDamage::with(['product','fulfillment','locationDamage.locationDescription'])->where('products_demage_id',$id)->first();
				$oldQty	= $oldDatas->qty;
				$oldProduct	= $oldDatas->qty;
				
				
				$inventory	= Inventory::where([["product_id",$oldDatas->product_id],["fulfillment_center_id",$oldDatas->fulfillment_center_id],["company_id",$oldDatas->product->company_id]])->first();
				if($inventory){
					$stock_hold			= $inventory->stock_hold - $oldQty;
					$stock_available	= $inventory->stock_available + $oldQty;
						
					$inventory->stock_hold		= $stock_hold;
					$inventory->stock_available	= $stock_available;
					$inventory->save();
				}
				
				LocationsDamageDetail::where("location_detail_damage_id",$oldDatas->locationDamage->location_detail_damage_id)->update(["location_id" => $request->location_id, "products_demage_id" => $id , "qty" => $request->qty]);
				
				$oldDatas->fulfillment_center_id	= $request->fulfillment_center_id;
				$oldDatas->product_id				= $request->product_code;
				$oldDatas->qty						= $request->qty;
				$oldDatas->reason					= $request->reason;
				$oldDatas->additional_reason		= $request->additional_reason;
				$oldDatas->hold_by					= $auth->name.' - '.$auth->company_id;
				$oldDatas->hold_date				= date('Y-m-d');
				$oldDatas->save();
				
				
				
				$inventoryPlus	= Inventory::where([["product_id",$request->product_code],["fulfillment_center_id",$request->fulfillment_center_id],["company_id",$request->company]])->first();
				if($inventoryPlus){
					$stock_hold			= $inventoryPlus->stock_hold + $request->qty;
					$stock_available	= $inventoryPlus->stock_available - $request->qty;
					
					$inventoryPlus->stock_hold		= $stock_hold;
					$inventoryPlus->stock_available	= $stock_available;
					$inventoryPlus->save();
				}
				
				return response()
					->json(['status'=>200 ,'datas' => ['message' => 'Update Successfully'], 'errors' => []])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(200);
				
			}else{
				return response()
					->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["product_code" => ["product code has been register"]]]])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(422);
			}
			
		}else{
			return response()
					->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["company" => ["company not has register"]]]])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(422);
			
		}
		
	}
	
    public function upload(Request $request){
		$auth		= $request->auth;
		$datasArray	= [];
		if($auth->company_id == "OMS"){			
			$this->validate($request, [
					'company'				=> 'required|max:255',
					'fulfillment_center_id'	=> 'required|integer|min:0|not_in:0',
					'location_id'			=> 'required|integer|min:0|not_in:0',
					'files'					=> 'required|mimes:xlsx,csv,txt'
				]);
				
			$company	= $request->company;
		}else{
			$this->validate($request, [
				'files'					=> 'required|mimes:xlsx,csv,txt',
				'fulfillment_center_id'	=> 'required|integer|min:0|not_in:0',
				'location_id'			=> 'required|integer|min:0|not_in:0',
			]);
			
			$company	= $auth->company_id;
		}
		
		$file = $request->file('files');
        $extension  =$request->file('files')->getClientOriginalExtension(); 
		if($file->getSize() <= 2000000){
			if($extension	=== 'xlsx'){
				
				/**  Identify the type of file  **/
				$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file);

				/**  Create a new Reader of the type that has been identified  **/
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

				/**  Load file to a Spreadsheet Object  **/
				$spreadsheet = $reader->load($file);

				/**  Convert Spreadsheet Object to an Array for ease of use  **/
				$arrays = $spreadsheet->getActiveSheet()->toArray();
				
				$headers = array_shift($arrays);
				$result = array_map(function($x) use ($headers){
					return array_combine($headers, $x);
				}, $arrays);
				
				$datasArray	= $result;
			}else{
				$delimiter 	= ',';
				$fp 		= fopen($file, 'r');
				$all_rows 	= array();
				$header 	= null;
				
				while ($row = fgetcsv($fp,1000,$delimiter)){
					if ($header === null) {
						$header = $row;
						continue;
                    }
                    
					$all_rows[] = array_combine($header, $row);
                }
				fclose($fp);
				
				$datasArray	= $all_rows;
			}
			
			$rules = [];

			foreach($datasArray as $key => $val){
				$rules[$key.'.product_code'] 		= 'required|max:255|regex:/^\S*$/';
				$rules[$key.'.qty'] 				= 'required|integer|min:0|not_in:0';
				$rules[$key.'.reason'] 		 		= 'required';
			}
			$validator = Validator::make($datasArray, $rules);
			if ($validator->fails()){
				return response()
						->json(['status'=>422 ,'datas' => [], 'errors' => ["message" => $validator->messages()]])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(422);
			}else{
				
				$check	= CompanyFulfillment::where([['company_id',$company],['fulfillment_center_id',$request->fulfillment_center_id]])->first();
				
				if($check->company_id == $auth->company_id || $auth->company_id == "OMS"){					
					$respons	= [];
					
					foreach($datasArray as $product){
						$getProduct	= Products::where([["company_id" , $company],["product_code" , $product["product_code"]],["type","NORMAL"]])->first();
						
						if($getProduct){
							$checkDamage	= ProductsDamage::where([["product_id", $getProduct->product_id], ["fulfillment_center_id", $request->fulfillment_center_id], ["status","HOLD"]])->first();
							if(!$checkDamage){
								
								$productDamage							= new ProductsDamage;
								$productDamage->fulfillment_center_id	= $request->fulfillment_center_id;
								$productDamage->product_id				= $getProduct->product_id;
								$productDamage->qty						= $product["qty"];
								$productDamage->reason					= $product["reason"];
								$productDamage->additional_reason		= $product["additional_reason"];
								$productDamage->hold_by					= $auth->name.' - '.$auth->company_id;
								$productDamage->hold_date				= date('Y-m-d');
								$productDamage->status					= 'HOLD';
								$productDamage->save();
					
					
								$productDamageId	= $productDamage->products_demage_id;
								
								$productDamageLocation						= new LocationsDamageDetail;
								$productDamageLocation->location_id			= $request->location_id;
								$productDamageLocation->products_demage_id	= $productDamageId;
								$productDamageLocation->qty					= $product["qty"];
								$productDamageLocation->save();
								
								$inventory	= Inventory::where([["product_id",$getProduct->product_id],["fulfillment_center_id",$request->fulfillment_center_id],["company_id",$company]])->first();
								if($inventory){
									$stock_hold			= $inventory->stock_hold + $product["qty"];
									$stock_available	= $inventory->stock_available - $product["qty"];
									
									$inventory->stock_hold		= $stock_hold;
									$inventory->stock_available	= $stock_available;
									$inventory->save();
								}
								
								$respons[]		= ['product_code' => $product["product_code"] , 'status' => 'success', "message" => "Successfully"];
							}else{
								$respons[]		= ['product_code' => $product["product_code"] , 'status' => 'error', "message" => "Product code has been register"];
							}
						}else{
							$respons[]		= ['product_code' => $product["product_code"] , 'status' => 'error', "message" => "Product code not register"];
						}
						
						usleep(250000);
					}
					
					
					return response()
							->json(['status'=>200 ,'datas' => ['message' => 'Upload Successfully' , 'datas' => $respons], 'errors' => []])
							->withHeaders([
							  'Content-Type'          => 'application/json',
							  ])
							->setStatusCode(200);
				}else{
					return response()
							->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["company" => ["company not has register"]]]])
							->withHeaders([
							  'Content-Type'          => 'application/json',
							  ])
							->setStatusCode(422);
				}
			}
		}else{
			return response()
					->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ["files" => ["file size maximum 2 MB."]]]])
					->withHeaders([
					  'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(422);
		}
	}
	
	
	
	public function download(Request $request){
		$auth					= $request->auth;
		
		$file_name				= $request->file_name;
        $company_id     		= $request->company_id;
        $product_description	= $request->product_description;
        $product_code			= $request->product_code;
        $status					= $request->status;
        $sort_field = "products_demage_id";
        $sort_type = "DESC";
		
		if($auth->company_id == "OMS"){
			$query = ProductsDamage::with(['product','fulfillment','locationDamage.locationDescription'])->orderBy($sort_field,$sort_type);
		}else{
			$query = ProductsDamage::with(['product','fulfillment','locationDamage.locationDescription'])->where('company_id', $auth->company_id)->orderBy($sort_field,$sort_type);
		}
		
			if ($company_id) {
				$like = "%{$company_id}%";
				$query = $query->whereHas('product', function($query) use ($like){
					$query->where('company_id', 'LIKE', $like);
				});
			}
					
			if ($product_code) {
				$like = "%{$product_code}%";
				$query = $query->whereHas('product', function($query) use ($like){
					$query->where('product_code', 'LIKE', $like);
				});
			}
					
			if ($product_description) {
				$like = "%{$product_description}%";
				$query = $query->whereHas('product', function($query) use ($like){
					$query->where('product_description', 'LIKE', $like);
				});
			}
			
					
			if ($status) {
				$query = $query->where('status',$status);
			}
			
		
		$datas	= $query->get();
		
		
		$file_path  	= storage_path('xlsx/download') . '/' . $file_name;
		
		
		$spreadsheet 	= new Spreadsheet();
		$sheet 			= $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', 'Company ID');
		$sheet->setCellValue('B1', 'Product Code');
		$sheet->setCellValue('C1', 'Product Description');
		$sheet->setCellValue('D1', 'Fulfillment');
		$sheet->setCellValue('E1', 'Location');
		$sheet->setCellValue('F1', 'Hold By');
		$sheet->setCellValue('G1', 'Hold Date');
		$sheet->setCellValue('H1', 'Sale By');
		$sheet->setCellValue('I1', 'Sale Date');
		$sheet->setCellValue('J1', 'Qty');
		$sheet->setCellValue('K1', 'Status');
		
		if(count($datas) > 0){
			$x=2;
			
			foreach($datas as $data){
				$sheet->setCellValue('A'.$x, $data->product->company_id);
				$sheet->setCellValue('B'.$x, $data->product_code);
				$sheet->setCellValue('C'.$x, $data->product_description);
				$sheet->setCellValue('D'.$x, $data->fulfillment->name.'('.$data->fulfillment->code.')');
				$sheet->setCellValue('E'.$x, $data->locationDamage->locationDescription->location_descriptions.'('.$data->locationDamage->locationDescription->location_code.')');
				$sheet->setCellValue('F'.$x, $data->hold_by);
				$sheet->setCellValue('G'.$x, $data->hold_date);
				$sheet->setCellValue('H'.$x, $data->sale_by);
				$sheet->setCellValue('I'.$x, $data->sale_date);
				$sheet->setCellValue('J'.$x, $data->qty);
				$sheet->setCellValue('K'.$x, $data->status);
				
				
				$x++;
			}
		}
		
		$writer = new Xlsx($spreadsheet);
		$writer->save($file_path); 
		 $headers	= ['Content-Type' => 'application/vnd.ms-excel', 'Content-Disposition' => 'attachment'];
		if (file_exists($file_path)) {
		  $file = file_get_contents($file_path);
		  $res = response($file, 200)->withHeaders(['Content-Type' => 'application/vnd.ms-excel', 'Content-Disposition' => 'attachment;filename="'.$file_name.'"']);
		   register_shutdown_function('unlink', $file_path);
		   return $res;
		}else{
			return response()
					->json(['status'=>500 ,'datas' => [], 'errors' => ['product_code' => 'download file error']])
					->withHeaders([
						'Content-Type'          => 'application/json',
					  ])
					->setStatusCode(500);
		}
		
    }
}