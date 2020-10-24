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
    $router->get('/test-queue',['as' => 'testQueue','uses' => 'IndexController@test_queue']);

});


$router->group(
    ['middleware' => 'jwt.auth'], 
    function() use ($router) {
		
		
		/*
		*	address
		*/
		$router->get('/country/index',['as' => 'countryIndex','uses' => 'CountryController@index']);
		$router->get('/province/index',['as' => 'provinceIndex','uses' => 'AddressController@province']);
		$router->get('/city/index',['as' => 'cityIndex','uses' => 'AddressController@city']);
		$router->get('/area/index',['as' => 'areaIndex','uses' => 'AddressController@area']);
		$router->get('/sub-area/index',['as' => 'subareaIndex','uses' => 'AddressController@subarea']);
		$router->get('/postal-code/index',['as' => 'postalCodeIndex','uses' => 'AddressController@postalCode']);
		
		/*
		*	Fulfillment
		*/
        $router->get('/fulfillment/index',['as' => 'fulfillmentIndex','uses' => 'FulfillmentCenterController@index']);
        $router->get('/fulfillment/detail/{id}',['as' => 'fulfillmentDetail','uses' => 'FulfillmentCenterController@detail']);
        $router->post('/fulfillment/add',['as' => 'fulfillmentAdd','uses' => 'FulfillmentCenterController@store']);
        $router->put('/fulfillment/update/{id}',['as' => 'fulfillmentUpdate','uses' => 'FulfillmentCenterController@update']);
        $router->put('/fulfillment/update-status/{id}',['as' => 'fulfillmentUpdate','uses' => 'FulfillmentCenterController@updateStatus']);
		
		/*
		*	Company
		*/
        $router->get('/company/index',['as' => 'companyIndex','uses' => 'CompanyController@index']);
        $router->get('/company/detail/{id}',['as' => 'companyDetail','uses' => 'CompanyController@detail']);
        $router->post('/company/add',['as' => 'companyAdd','uses' => 'CompanyController@store']);
        $router->put('/company/update/{id}',['as' => 'companyUpdate','uses' => 'CompanyController@update']);
        $router->put('/company/update-status/{id}',['as' => 'companyUpdate','uses' => 'CompanyController@updateStatus']);
		
		/*
		*	Products
		*/
        $router->get('/products/normal/template/{type}',['as' => 'templateNormalProducts','uses' => 'FilesController@productDownload']);
        $router->post('/products/normal/upload',['as' => 'uploadNormalProducts','uses' => 'ProductsController@uploadNormalProducts']);
        $router->get('/products/normal/index',['as' => 'normalProducts','uses' => 'ProductsController@normalProducts']);
        $router->get('/products/normal/detail/{id}',['as' => 'normalDetailProducts','uses' => 'ProductsController@normalDetailProducts']);
        $router->put('/products/normal/update/{id}',['as' => 'normalUpdateProducts','uses' => 'ProductsController@normalUpdateProducts']);
        $router->post('/products/normal/add',['as' => 'normalAddProducts','uses' => 'ProductsController@normalAddProducts']);
        $router->post('/products/normal/download',['as' => 'downloadProducts','uses' => 'ProductsController@downloadProducts']);
		
		/*
		*	UOM
		*/
        $router->get('/uom/index',['as' => 'uomIndex','uses' => 'UomController@index']);
		
    }
);

