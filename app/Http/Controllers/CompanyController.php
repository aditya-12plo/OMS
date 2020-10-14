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
use App\Models\Company;

class CompanyController extends Controller
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
        $name     				= $request->name;
        $status     			= $request->status;
		
        if(!$sort_field){
            $sort_field = "company_id";
            $sort_type = "DESC";
        }
		
		if($auth->company_id == "OMS"){
			$query = Company::orderBy($sort_field,$sort_type);
		}else{
			$query = Company::where('company_id', $auth->company_id)->orderBy($sort_field,$sort_type);			
		}
		
		if ($company_id) {
            $like = "%{$company_id}%";
            $query = $query->where('company_id', 'LIKE', $like);
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
	
}