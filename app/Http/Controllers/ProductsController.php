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
use App\Models\Products;
use App\Models\FulfillmentCenter;
use App\Models\CompanyFulfillment;
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
	
	
	
    public function normalDetailProducts(Request $request, $id){
		$auth	= $request->auth;
		$query = Products::with(['company','uom_description','inventory.fulfillment'])->where("product_id",$id)->first();
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
	
	
    public function uploadNormalProducts(Request $request){
		$auth		= $request->auth;
		$datasArray	= [];
		
		if($auth->company_id == "OMS"){			
			$this->validate($request, [
					'company'	=> 'required|max:255',
					'files'		=> 'required|mimes:xlsx,csv,txt'
				]);
				
			$company	= $request->company;
		}else{
			$this->validate($request, [
				'files'		=> 'required|mimes:xlsx,csv,txt'
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
				$rules[$key.'.product_name'] 		= 'required|max:255';
				$rules[$key.'.uom_code'] 		 	= 'required|in:EA,KG,PLT,PLT';
				$rules[$key.'.price'] 		 		= 'required|regex:/^\d*(\.\d{1,2})?$/';
				$rules[$key.'.width'] 		 		= 'required|regex:/^\d*(\.\d{1,2})?$/';
				$rules[$key.'.height'] 		 		= 'required|regex:/^\d*(\.\d{1,2})?$/';
				$rules[$key.'.weight'] 		 		= 'required|regex:/^\d*(\.\d{1,2})?$/';
				$rules[$key.'.net_weight'] 		 	= 'required|regex:/^\d*(\.\d{1,2})?$/';
				$rules[$key.'.gross_weight'] 		= 'required|regex:/^\d*(\.\d{1,2})?$/';
				$rules[$key.'.qty_per_carton']		= 'required|regex:/^\d*(\.\d{1,2})?$/';
				$rules[$key.'.carton_per_pallet']	= 'required|regex:/^\d*(\.\d{1,2})?$/';
				$rules[$key.'.cube'] 		 		= 'required|regex:/^\d*(\.\d{1,2})?$/';
				$rules[$key.'.currency'] 			= 'required|max:255';
				$rules[$key.'.barcode'] 			= 'max:255';
				$rules[$key.'.time_to_live'] 		= 'required|in:0,1';
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
				$response	= [];
				
				foreach($datasArray as $product){
					$check = Products::where([['company_id' , $company], ['product_code' , trim($product['product_code'])], ['type' , 'NORMAL']])->first();
					if($check){
						$check->product_code		= trim($product["product_code"]);
						$check->product_description	= $product["product_name"];
						$check->uom_code			= $product["uom_code"];
						$check->price				= $product["price"];
						$check->width				= $product["width"];
						$check->height				= $product["height"];
						$check->weight				= $product["weight"];
						$check->net_weight			= $product["net_weight"];
						$check->gross_weight		= $product["gross_weight"];
						$check->qty_per_carton		= $product["qty_per_carton"];
						$check->carton_per_pallet	= $product["carton_per_pallet"];
						$check->cube				= $product["cube"];
						$check->currency			= $product["currency"];
						$check->barcode				= trim($product["barcode"]);
						$check->time_to_live		= $product["time_to_live"];
						$check->save();
						
						$productId	= $check->product_id;
						
						$fulfillments = CompanyFulfillment::where('company_id', $company)->get();
						if(count($fulfillments) > 0){
							foreach($fulfillments as $fulfillment){
								$checkInventory		= Inventory::where([['product_id',$productId],['fulfillment_center_id',$fulfillment->fulfillment_center_id],['company_id',$company]])->first();
								if(!$checkInventory){
									$inventory								= new Inventory;
									$inventory->product_id	 				= $productId;
									$inventory->fulfillment_center_id	 	= $fulfillment->fulfillment_center_id;
									$inventory->company_id	 				= $company;
									$inventory->stock_on_hand 				= 0;
									$inventory->stock_available				= 0;
									$inventory->stock_hold	 				= 0;
									$inventory->stock_booked 				= 0;
									$inventory->save();
								}								
							}
						}
						
						$respons[]		= ['product_code' => $product["product_code"] , 'status' => 'update data'];
					}else{
						$insert						= new Products;
						$insert->product_code		= trim($product["product_code"]);
						$insert->company_id 		= $company;
						$insert->product_description= $product["product_name"];
						$insert->uom_code			= $product["uom_code"];
						$insert->price				= $product["price"];
						$insert->width				= $product["width"];
						$insert->height				= $product["height"];
						$insert->weight				= $product["weight"];
						$insert->net_weight			= $product["net_weight"];
						$insert->gross_weight		= $product["gross_weight"];
						$insert->qty_per_carton		= $product["qty_per_carton"];
						$insert->carton_per_pallet	= $product["carton_per_pallet"];
						$insert->cube				= $product["cube"];
						$insert->currency			= $product["currency"];
						$insert->barcode			= trim($product["barcode"]);
						$insert->time_to_live		= $product["time_to_live"];
						$insert->type				= 'NORMAL';
						$insert->save();
						
						
						$productId	= $insert->product_id;
						
						$fulfillments = CompanyFulfillment::where('company_id', $company)->get();
						if(count($fulfillments) > 0){
							foreach($fulfillments as $fulfillment){
								$inventory								= new Inventory;
								$inventory->product_id	 				= $productId;
								$inventory->fulfillment_center_id	 	= $fulfillment->fulfillment_center_id;
								$inventory->company_id	 				= $company;
								$inventory->stock_on_hand 				= 0;
								$inventory->stock_available				= 0;
								$inventory->stock_hold	 				= 0;
								$inventory->stock_booked 				= 0;
								$inventory->save();
								
							}
						}
						
						$respons[]		= ['product_code' => $product["product_code"] , 'status' => 'insert data'];

					}
					usleep(250000);
				}
				
				return response()
						->json(['status'=>200 ,'datas' => ['message' => 'Upload Successfully' , 'datas' => $respons], 'errors' => []])
						->withHeaders([
						  'Content-Type'          => 'application/json',
						  ])
						->setStatusCode(200);
				
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
				
				$fulfillments = CompanyFulfillment::where('company_id', $request->company)->get();
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
	
	
    public function downloadProducts(Request $request){
		
		$auth					= $request->auth;
		$file_name				= $request->file_name;
		$product_description	= $request->product_description;
        $product_code			= $request->product_code;
        $company_id     		= $request->company_id;
		$sort_field 			= "product_id";
        $sort_type 				= "DESC";
			
			
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
		}
		
		$datas	= $query->get();
		
		
		$file_path  	= storage_path('xlsx/download') . '/' . $file_name;
		
		$spreadsheet 	= new Spreadsheet();
		$sheet 			= $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', 'Company ID');
		$sheet->setCellValue('B1', 'Product Code');
		$sheet->setCellValue('C1', 'Product Description');
		$sheet->setCellValue('D1', 'UOM');
		$sheet->setCellValue('E1', 'Price');
		$sheet->setCellValue('F1', 'Width');
		$sheet->setCellValue('G1', 'Height');
		$sheet->setCellValue('H1', 'Weight');
		$sheet->setCellValue('I1', 'Net Weight');
		$sheet->setCellValue('J1', 'Gross Weight');
		$sheet->setCellValue('K1', 'Qty Per Carton');
		$sheet->setCellValue('L1', 'Carton Per Pallet');
		$sheet->setCellValue('M1', 'Cube');
		$sheet->setCellValue('N1', 'Currency');
		$sheet->setCellValue('O1', 'Barcode');
		$sheet->setCellValue('P1', 'Time To Live');
		$sheet->setCellValue('Q1', 'Fulfillment');
		$sheet->setCellValue('R1', 'Stock Available');
		$sheet->setCellValue('S1', 'Stock On Hand');
		$sheet->setCellValue('T1', 'Stock Hold');
		$sheet->setCellValue('U1', 'Stock Booked');
		$sheet->setCellValue('V1', 'Last Update Stock');
		
		if(count($datas) > 0){
			$x=2;
			foreach($datas as $data){
				foreach($data->inventory as $inventory){
					$sheet->setCellValue('A'.$x, $data->company->name.' ( '.$data->company->company_id.' )');
					$sheet->setCellValue('B'.$x, $data->product_code);
					$sheet->setCellValue('C'.$x, $data->product_description);
					$sheet->setCellValue('D'.$x, $data->uom_description->uom_description.' ( '.$data->uom_description->uom_code.' )');
					$sheet->setCellValue('E'.$x, $data->price);
					$sheet->setCellValue('F'.$x, $data->width);
					$sheet->setCellValue('G'.$x, $data->height);
					$sheet->setCellValue('H'.$x, $data->weight);
					$sheet->setCellValue('I'.$x, $data->net_weight);
					$sheet->setCellValue('J'.$x, $data->gross_weight);
					$sheet->setCellValue('K'.$x, $data->qty_per_carton);
					$sheet->setCellValue('L'.$x, $data->carton_per_pallet);
					$sheet->setCellValue('M'.$x, $data->cube);
					$sheet->setCellValue('N'.$x,  $data->currency);
					$sheet->setCellValue('O'.$x, $data->barcode);
					$sheet->setCellValue('P'.$x, $data->time_to_live);
					$sheet->setCellValue('Q'.$x, $inventory->fulfillment->name.' ( '.$inventory->fulfillment->code.' )');
					$sheet->setCellValue('R'.$x, $inventory->stock_available);
					$sheet->setCellValue('S'.$x, $inventory->stock_on_hand);
					$sheet->setCellValue('T'.$x, $inventory->stock_hold);
					$sheet->setCellValue('U'.$x, $inventory->stock_booked);
					$sheet->setCellValue('V'.$x, $inventory->updated_at);
					
					$x++;
				}
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