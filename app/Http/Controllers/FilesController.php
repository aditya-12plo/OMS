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


class FilesController extends Controller
{

    public function productDownload($type)
    {
      $xlsx         = "normal_products_template.xlsx";
      $csv         	= "normal_products_template.csv";
	  
	  if($type == 'xlsx'){
		$name			= $xlsx;
		$avatar_path  	= storage_path($type) . '/' . $xlsx;
		$appType 		= 'application/vnd.ms-excel';
		$headers 		= ['Content-Type' => $appType];
		  
	  }else{
		$name			= $csv;
		$avatar_path  	= storage_path($type) . '/' . $csv;
		$appType 		= 'text/csv';
		$headers 		= ['Content-Type' => $appType];
		  
	  }
	  
      
      if (file_exists($avatar_path)) {
        $file = file_get_contents($avatar_path);
        return response($file, 200)->withHeaders(['Content-Type' => $appType, 'Content-Disposition' => 'attachment', 'filename' => $name]);
      }else{
		return response()
			->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ['File Not Found']]])
			->withHeaders([
			  'Content-Type'          => 'application/json',
			  ])
				->setStatusCode(422);
      }
    }

    public function productBundleDownload($type)
    {
      $xlsx         = "bundle_products_template.xlsx";
      $csv         	= "bundle_products_template.csv";
	  
	  if($type == 'xlsx'){
		$name			= $xlsx;
		$avatar_path  	= storage_path($type) . '/' . $xlsx;
		$appType 		= 'application/vnd.ms-excel';
		$headers 		= ['Content-Type' => $appType];
		  
	  }else{
		$name			= $csv;
		$avatar_path  	= storage_path($type) . '/' . $csv;
		$appType 		= 'text/csv';
		$headers 		= ['Content-Type' => $appType];
		  
	  }
	  
      
      if (file_exists($avatar_path)) {
        $file = file_get_contents($avatar_path);
        return response($file, 200)->withHeaders(['Content-Type' => $appType, 'Content-Disposition' => 'attachment', 'filename' => $name]);
      }else{
		return response()
			->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ['File Not Found']]])
			->withHeaders([
			  'Content-Type'          => 'application/json',
			  ])
				->setStatusCode(422);
      }
    }

    public function productDamageDownload($type)
    {
      $xlsx         = "damage_products_template.xlsx";
      $csv         	= "damage_products_template.csv";
	  
	  if($type == 'xlsx'){
		$name			= $xlsx;
		$avatar_path  	= storage_path($type) . '/' . $xlsx;
		$appType 		= 'application/vnd.ms-excel';
		$headers 		= ['Content-Type' => $appType];
		  
	  }else{
		$name			= $csv;
		$avatar_path  	= storage_path($type) . '/' . $csv;
		$appType 		= 'text/csv';
		$headers 		= ['Content-Type' => $appType];
		  
	  }
	  
      
      if (file_exists($avatar_path)) {
        $file = file_get_contents($avatar_path);
        return response($file, 200)->withHeaders(['Content-Type' => $appType, 'Content-Disposition' => 'attachment', 'filename' => $name]);
      }else{
		return response()
			->json(['status'=>422 ,'datas' => [], 'errors' => ['message' => ['File Not Found']]])
			->withHeaders([
			  'Content-Type'          => 'application/json',
			  ])
				->setStatusCode(422);
      }
    }
}