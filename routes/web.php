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

Route::get('/', function () {
    phpinfo();
});

Route::prefix('/delete')->group(function () {
    Route::get('/index', 'Tools\\deleteController@index');
    Route::post('/createExcel', 'Tools\\deleteController@createExcel');
});

Route::prefix('/select')->group(function () {
    Route::get('/index', 'Tools\\selectController@index');
    Route::post('/createExcel', 'Tools\\selectController@createExcel');
});

