<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->group(['prefix' => 'api/v1/testing','middleware'=>'auth'], function() use ($router){
	
	
	$router->get('/transaction', ['uses' => 'TransactionController@index']);
	$router->post('/transaction', ['uses' => 'TransactionController@store']);
	$router->put('/transaction/{id}', ['uses' => 'TransactionController@update']);
	$router->get('/transaction/{id}', ['uses' => 'TransactionController@show']);
	
	$router->get('/list-transaction-by-product/{id}', ['uses' => 'ProductController@listTransaction']);
	
	$router->get('/', ['uses' => 'ProductController@index']);
	$router->post('/', ['uses' => 'ProductController@store']);
	$router->get('/{id}', ['uses' => 'ProductController@show']);
	$router->delete('/{id}', ['uses' => 'ProductController@destroy']);
	$router->put('/{id}', ['uses' => 'ProductController@update']);

});
