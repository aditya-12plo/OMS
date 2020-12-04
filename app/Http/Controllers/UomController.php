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
use App\Models\Uom;

class UomController extends Controller
{
	public function __construct(){
		$this->middleware('jwt.auth');
    }
	
	
    public function index(Request $request){
		$auth					= $request->auth;
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $uom_code     		= $request->uom_code;
        $uom_description	= $request->uom_description;
		
        if(!$sort_field){
            $sort_field = "uom_code";
            $sort_type = "DESC";
        }
		
		$query = Uom::orderBy($sort_field,$sort_type);
							
		if ($uom_code) {
			$like = "%{$uom_code}%";
			$query = $query->where('uom_code', 'LIKE', $like);
		}
					
		if ($uom_description) {
			$like = "%{$uom_description}%";
			$query = $query->where('uom_description', 'LIKE', $like);
		}
		
		
		return $query->paginate($perPage);
    }
	 
}