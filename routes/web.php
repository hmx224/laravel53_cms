<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/


Route::group(['middleware' => 'web'], function () {
    Route::get('/', 'HomeController@index');
    Route::get('index.html', 'HomeController@index');
    Route::get('{module}/{date}/detail-{id}.shtml', 'WebController@detail');

    Route::get('{module}/{path1}/{template}-{page}.shtml', 'WebController@list1');
    Route::get('{module}/{path1}/{path2}/{template}-{page}.shtml', 'WebController@list2');
    Route::get('{module}/{path1}/{path2}/{path3}/{template}-{page}.shtml', 'WebController@list3');
    Route::get('{module}/{path1}/{path2}/{path3}/{path4}/{template}-{page}.shtml', 'WebController@list4');

    Route::get('{module}/{path1}/{template}.shtml', 'WebController@list1');
    Route::get('{module}/{path1}/{path2}/{template}.shtml', 'WebController@list2');
    Route::get('{module}/{path1}/{path2}/{path3}/{template}.shtml', 'WebController@list3');
    Route::get('{module}/{path1}/{path2}/{path3}/{path4}/{template}.shtml', 'WebController@list4');

    Route::get('/mall', function () {
        return view('admin.mall.index');
    });
    Route::get('/mall/{all}', function () {
        return view('admin.mall.index');
    })->where(['all' => '.*']);
});