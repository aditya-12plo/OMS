<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/',['as' => 'index','uses' => 'IndexController@index']);


$router->post('/login',['as' => 'userLogin','uses' => 'Auth\LoginController@authenticate']);



$router->group(['prefix' => 'root-system'], function () use ($router) {
	
    $router->get('/version', function () use ($router) {
        return $router->app->version();
    });
    $router->get('/pdf',['as' => 'indexpdf','uses' => 'IndexController@pdf']);
    $router->get('/pdfencode',['as' => 'pdfencode','uses' => 'IndexController@pdfencode']);
    $router->get('/pdfdencode',['as' => 'pdfdencode','uses' => 'IndexController@pdfdencode']);
    $router->get('/test-log',['as' => 'testLog','uses' => 'IndexController@testLog']);

});


$router->group(
    ['middleware' => 'jwt.auth'], 
    function() use ($router) {
		
		/*
		*	Country
		*/
		$router->get('/country/index',['as' => 'countryIndex','uses' => 'CountryController@index']);
		
		/*
		*	Fulfillment
		*/
        $router->get('/fulfillment/index',['as' => 'fulfillmentIndex','uses' => 'FulfillmentCenterController@index']);
        $router->post('/fulfillment/add',['as' => 'fulfillmentAdd','uses' => 'FulfillmentCenterController@store']);
        $router->put('/fulfillment/update/{id}',['as' => 'fulfillmentUpdate','uses' => 'FulfillmentCenterController@update']);
        $router->put('/fulfillment/update-status/{id}',['as' => 'fulfillmentUpdate','uses' => 'FulfillmentCenterController@updateStatus']);
		
		/*
		*	Company
		*/
        $router->get('/company/index',['as' => 'companyIndex','uses' => 'CompanyController@index']);
        $router->post('/company/add',['as' => 'companyAdd','uses' => 'CompanyController@store']);
        $router->put('/company/update/{id}',['as' => 'companyUpdate','uses' => 'CompanyController@update']);
        $router->put('/company/update-status/{id}',['as' => 'companyUpdate','uses' => 'CompanyController@updateStatus']);
		
		
    }
);

