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
use App\Models\Address;

class AddressController extends Controller
{
	public function __construct(){
		$this->middleware('jwt.auth');
    }
	
	 public function province(Request $request){
		$auth					= $request->auth;
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $country     			= $request->country;
        $province				= $request->province;
		
        if(!$sort_field){
            $sort_field = "id";
            $sort_type = "ASC";
        }
		
		$query = Address::distinct()->select('country_name','province')->orderBy($sort_field,$sort_type);
		
		
		if ($country) {
			$like = "%{$country}%";
			$query = $query->where('country_name', 'LIKE', $like);
		}
					
		if ($province) {
			$like = "%{$province}%";
			$query = $query->where('province', 'LIKE', $like);
		}
		return $query->paginate($perPage);
	 }
	
	 public function city(Request $request){
		$auth					= $request->auth;
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $country     			= $request->country;
        $province				= $request->province;
        $city					= $request->city;
		
        if(!$sort_field){
            $sort_field = "id";
            $sort_type = "ASC";
        }
		
		$query = Address::distinct()->select('country_name','province','city')->orderBy($sort_field,$sort_type);
		
		
		if ($country) {
			$like = "%{$country}%";
			$query = $query->where('country_name', 'LIKE', $like);
		}
					
		if ($province) {
			$like = "%{$province}%";
			$query = $query->where('province', 'LIKE', $like);
		}
					
		if ($city) {
			$like = "%{$city}%";
			$query = $query->where('city', 'LIKE', $like);
		}
		return $query->paginate($perPage);
	 }
	
	 public function area(Request $request){
		$auth					= $request->auth;
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $country     			= $request->country;
        $province				= $request->province;
        $city					= $request->city;
        $area					= $request->area;
		
        if(!$sort_field){
            $sort_field = "id";
            $sort_type = "ASC";
        }
		
		$query = Address::distinct()->select('country_name','province','city','area')->orderBy($sort_field,$sort_type);
		
		
		if ($country) {
			$like = "%{$country}%";
			$query = $query->where('country_name', 'LIKE', $like);
		}
					
		if ($province) {
			$like = "%{$province}%";
			$query = $query->where('province', 'LIKE', $like);
		}
					
		if ($city) {
			$like = "%{$city}%";
			$query = $query->where('city', 'LIKE', $like);
		}
					
		if ($area) {
			$like = "%{$area}%";
			$query = $query->where('area', 'LIKE', $like);
		}
		
		return $query->paginate($perPage);
	 }
	
	 public function subarea(Request $request){
		$auth					= $request->auth;
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $country     			= $request->country;
        $province				= $request->province;
        $city					= $request->city;
        $area					= $request->area;
        $sub_area				= $request->sub_area;
		
        if(!$sort_field){
            $sort_field = "id";
            $sort_type = "ASC";
        }
		
		$query = Address::distinct()->select('country_name','province','city','area','sub_area')->orderBy($sort_field,$sort_type);
		
		
		if ($country) {
			$like = "%{$country}%";
			$query = $query->where('country_name', 'LIKE', $like);
		}
					
		if ($province) {
			$like = "%{$province}%";
			$query = $query->where('province', 'LIKE', $like);
		}
					
		if ($city) {
			$like = "%{$city}%";
			$query = $query->where('city', 'LIKE', $like);
		}
					
		if ($area) {
			$like = "%{$area}%";
			$query = $query->where('area', 'LIKE', $like);
		}
					
		if ($sub_area) {
			$like = "%{$sub_area}%";
			$query = $query->where('sub_area', 'LIKE', $like);
		}
		
		return $query->paginate($perPage);
	 }
	
	 public function postalCode(Request $request){
		$auth					= $request->auth;
        $perPage        		= $request->per_page;
        $sort_field     		= $request->sort_field;
        $sort_type      		= $request->sort_type;
		
        $country     			= $request->country;
        $province				= $request->province;
        $city					= $request->city;
        $area					= $request->area;
        $sub_area				= $request->sub_area;
        $postal_code				= $request->postal_code;
		
        if(!$sort_field){
            $sort_field = "id";
            $sort_type = "ASC";
        }
		
		$query = Address::distinct()->select('country_name','province','city','area','sub_area','postal_code')->orderBy($sort_field,$sort_type);
		
		
		if ($country) {
			$like = "%{$country}%";
			$query = $query->where('country_name', 'LIKE', $like);
		}
					
		if ($province) {
			$like = "%{$province}%";
			$query = $query->where('province', 'LIKE', $like);
		}
					
		if ($city) {
			$like = "%{$city}%";
			$query = $query->where('city', 'LIKE', $like);
		}
					
		if ($area) {
			$like = "%{$area}%";
			$query = $query->where('area', 'LIKE', $like);
		}
					
		if ($sub_area) {
			$like = "%{$sub_area}%";
			$query = $query->where('sub_area', 'LIKE', $like);
		}
					
		if ($postal_code) {
			$like = "%{$postal_code}%";
			$query = $query->where('postal_code', 'LIKE', $like);
		}
		
		return $query->paginate($perPage);
	 }
	
}