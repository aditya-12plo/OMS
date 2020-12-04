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
use App\Models\Locations;
use App\Models\CompanyFulfillment;

class LocationsController extends Controller
{
	public function __construct(){
		$this->middleware('jwt.auth');
    }
	
    public function index(Request $request){
		$auth					= $request->auth;
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $location_code     	= $request->location_code;
        $location_descriptions	= $request->location_descriptions;
        $fulfillment_center_id	= $request->fulfillment_center_id;
		
        if(!$sort_field){
            $sort_field = "location_id";
            $sort_type = "DESC";
        }
		
		if($auth->company_id == "OMS"){
			$query = Locations::with('fulfillment')->orderBy($sort_field,$sort_type);
				
				
		}else{
			$query = Locations::with(['fulfillmentCompany'=> function($query) use ($like){
					$query->where('company_id',$auth->company_id);
				},'fulfillment'])->orderBy($sort_field,$sort_type);
		}
		
		if ($fulfillment_center_id) {
			$query = $query->where('fulfillment_center_id', $fulfillment_center_id);
		}
			
		if ($location_code) {
			$like = "%{$location_code}%";
			$query = $query->where('location_code', 'LIKE', $like);
		}
					
		if ($location_descriptions) {
			$like = "%{$location_descriptions}%";
			$query = $query->where('location_descriptions', 'LIKE', $like);
		}
		
		
		return $query->paginate($perPage);
    }
	
    public function companyFulfillments(Request $request){
		$auth					= $request->auth;
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $fulfillment_name	= $request->fulfillment_name;
        $company_id				= $request->company_id;
		
        if(!$sort_field){
            $sort_field = "company_fulfillment_id";
            $sort_type = "DESC";
        }
		
		if($auth->company_id == "OMS"){
			$query = CompanyFulfillment::with(['fulfillment.locations','company'])->orderBy($sort_field,$sort_type);
		
			
		}else{
			$query = CompanyFulfillment::where('company_id',$auth->company_id)->with(['fulfillment.locations','company'])->orderBy($sort_field,$sort_type);
			
		}
		
		if($company_id) {
			$like = "%{$company_id}%";
			$query = $query->where('company_id', 'LIKE', $like);
		}
		
		if($fulfillment_name) {
			$like = "%{$fulfillment_name}%";
			$query = $query->whereHas('fulfillment', function($query) use ($like){
				$query->where('name', 'LIKE', $like);
			});
		}
		return $query->paginate($perPage);
    }
	
	
}