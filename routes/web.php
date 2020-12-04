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
    $router->get('/php-aes-gcm',['as' => 'testQueue','uses' => 'IndexController@testAesGcm']);

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
		*	Fulfillment Center Type
		*/
        $router->get('/fulfillment-type/get-all',['as' => 'fulfillmentCenterTypeGetAl','uses' => 'FulfillmentCenterTypeController@getAllType']);
        $router->get('/fulfillment-type/index',['as' => 'fulfillmentCenterTypeIndex','uses' => 'FulfillmentCenterTypeController@index']);
        $router->get('/fulfillment-type/detail/{id}',['as' => 'fulfillmentCenterTypeDetail','uses' => 'FulfillmentCenterTypeController@detail']);
        $router->post('/fulfillment-type/add',['as' => 'fulfillmentCenterTypeAdd','uses' => 'FulfillmentCenterTypeController@store']);
        $router->put('/fulfillment-type/update/{id}',['as' => 'fulfillmentCenterTypeUpdate','uses' => 'FulfillmentCenterTypeController@update']);
  
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
		
		$router->get('/products/bundle/index',['as' => 'bundleProducts','uses' => 'ProductsController@bundleProducts']);
		$router->post('/products/bundle/add',['as' => 'bundleAddProducts','uses' => 'ProductsController@bundleAddProducts']);
		$router->get('/products/bundle/detail/{id}',['as' => 'bundleDetailProducts','uses' => 'ProductsController@bundleDetailProducts']);
        $router->put('/products/bundle/update/{id}',['as' => 'bundleUpdateProducts','uses' => 'ProductsController@bundleUpdateProducts']);
        $router->get('/products/bundle/template/{type}',['as' => 'templateBundleProducts','uses' => 'FilesController@productBundleDownload']);
        $router->post('/products/bundle/upload',['as' => 'uploadBundleProducts','uses' => 'ProductsController@uploadBundleProducts']);
        $router->post('/products/bundle/download',['as' => 'downloadBundleProducts','uses' => 'ProductsController@downloadBundleProducts']);
		
		$router->get('/products/damage/index',['as' => 'damageProducts','uses' => 'ProductsDamageController@index']);
		$router->get('/products/damage/detail/{id}',['as' => 'damageDetailProducts','uses' => 'ProductsDamageController@detail']);
        $router->post('/products/damage/add',['as' => 'damageAddProducts','uses' => 'ProductsDamageController@store']);
        $router->post('/products/damage/upload',['as' => 'damageUploadProducts','uses' => 'ProductsDamageController@upload']);
        $router->post('/products/damage/update-status',['as' => 'damageUpdateStatusProducts','uses' => 'ProductsDamageController@updateStatus']);
        $router->post('/products/damage/download',['as' => 'damageDownloadProducts','uses' => 'ProductsDamageController@download']);
        $router->put('/products/damage/update/{id}',['as' => 'damageUpdateProducts','uses' => 'ProductsDamageController@update']);
        $router->get('/products/damage/template/{type}',['as' => 'templateDamageProducts','uses' => 'FilesController@productDamageDownload']);
		
		
		/*
		*	Locations
		*/
        $router->get('/locations/index',['as' => 'locationIndex','uses' => 'LocationsController@index']);
        $router->get('/locations/company-fulfillments',['as' => 'locationTotal','uses' => 'LocationsController@companyFulfillments']);
		
		
		/*
		*	UOM
		*/
        $router->get('/uom/index',['as' => 'uomIndex','uses' => 'UomController@index']);
		
    }
);

