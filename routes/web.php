<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/** @var \Illuminate\Routing\Router $router */

//Route::get('/', function () {
//    return view('welcome');
//});

$router->group([], function ($router) {
    //mongodb
    $router->get('mongoDo', 'MongodbController@mongoDo');
    $router->get('insert', 'MongodbController@insert');

    //es
    $router->get('setIndex', 'EsController@setIndex');
    $router->get('addEsData', 'EsController@addEsData');
    $router->get('addOneData', 'EsController@addOneData');
    $router->get('deleteData', 'EsController@deleteData');
    $router->get('deleteMultiData', 'EsController@deleteMultiEsData');
    $router->get('getFilterData', 'EsController@getFilterData');
    $router->get('getFilterPageData', 'EsController@getFilterPageData');

    //mq
    $router->get('testQueue', 'MqController@testQueue');
    $router->get('mqConsumer', 'MqController@mqConsumer');

});
