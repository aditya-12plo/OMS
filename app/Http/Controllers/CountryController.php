<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use View,Input,Session,Validator,File,Hash,DB,Mail;
use Illuminate\Support\Facades\Crypt;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Log;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Models\User;
use App\Models\Country;

class CountryController extends Controller
{
	public function __construct(){
		$this->middleware('jwt.auth');
    }
	
	
    public function index(Request $request){
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $name     				= $request->name;
		
        if(!$sort_field){
            $sort_field = "id";
            $sort_type = "DESC";
        }
		
		$query = Country::orderBy($sort_field,$sort_type);
		
		if ($name) {
            $like = "%{$name}%";
            $query = $query->where('name', 'LIKE', $like);
        }
		
		return $query->paginate($perPage);
    }
	
	
}