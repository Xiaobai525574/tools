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

Route::get('/phpinfo', function () {
    phpinfo();
});

Route::get('/', 'Tools\\toolsController@index');
Route::get('/updateInfo', 'Tools\\toolsController@updateInfo');

Route::prefix('/delete')->group(function () {
    Route::get('/index', 'Tools\\deleteController@index');
    Route::post('/getExcel', 'Tools\\deleteController@getExcel');
    Route::post('/getCode', 'Tools\\deleteController@getCode');

});

Route::prefix('/select')->group(function () {
    Route::get('/index', 'Tools\\selectController@index');
    Route::post('/getExcel', 'Tools\\selectController@getExcel');
    Route::any('/getExcelByParameters', 'Tools\\selectController@getExcelByParameters');
    Route::post('/getCode', 'Tools\\selectController@getCode');
    Route::post('/getCodeByParameters', 'Tools\\selectController@getCodeByParameters');
});

