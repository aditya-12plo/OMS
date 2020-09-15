<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use PDF;

use App\Models\User;

class IndexController extends Controller
{
   	
/* 
error status code
	200	success
	404	Not Found (page or other resource doesn’t exist)
	401	Not authorized (not logged in)
	403	Logged in but access to requested area is forbidden
	400	Bad request (something wrong with URL or parameters)
	422	Unprocessable Entity (validation failed)
	500	General server error
*/
    public function __construct()
    {
        //
    }

    public function index()
    {
		return response()
		->json(['status'=>200 ,'datas' => ['message' => 'API Order Management System'], 'errors' => []])
		->setStatusCode(200);
    }

    public function pdf()
    {

      /**
       * for save pdf file to server
       */
      // $html = "<h1>Test</h1>";
      // return PDF::loadHTML($html)->setPaper('a4', 'landscape')->setWarnings(false)->save('pdf/coba.pdf');
      

      /**
       * for download pdf file
       */
      $data = ["satu" => 1];
      return PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('pdf.coba', $data)->download('invoice.pdf');

    }
}
