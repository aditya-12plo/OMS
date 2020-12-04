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

use App\Models\FulfillmentCenterType;

class FulfillmentCenterTypeController extends Controller
{
	public function __construct(){
		$this->middleware('jwt.auth');
    }

    public function index(Request $request){
		$auth					= $request->auth;
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $fulfillment_center_type_id     		= $request->fulfillment_center_type_id;
        $fulfillment_center_type_description    = $request->fulfillment_center_type_description;
        if(!$sort_field){
            $sort_field = "fulfillment_center_type_id";
            $sort_type = "DESC";
        }
		
		if($auth->company_id == "OMS"){
			$query = FulfillmentCenterType::orderBy($sort_field,$sort_type);
		
		
			if ($fulfillment_center_type_id) {
				$like = "%{$fulfillment_center_type_id}%";
				$query = $query->where('fulfillment_center_type_id', 'LIKE', $like);
			}
					
			if ($fulfillment_center_type_description) {
				$like = "%{$fulfillment_center_type_description}%";
				$query = $query->where('fulfillment_center_type_description', 'LIKE', $like);
            }
            
			return $query->paginate($perPage);
		}else{
			return array();
		}

	}
	

    public function getAllType(){
		$result = FulfillmentCenterType::select('fulfillment_center_type_id')->orderBy("fulfillment_center_type_id","DESC")->pluck('fulfillment_center_type_id');
		return $result;
	}



}