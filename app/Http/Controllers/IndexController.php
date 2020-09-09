<?php

namespace App\Http\Controllers;

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
}
