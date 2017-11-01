<?php

/**
* 页面
*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Modules\Page\Web'], function () {
    Route::get('pages/table', 'PageController@table');
    Route::post('pages/state', 'PageController@state');
    Route::get('pages/sort', 'PageController@sort');
    Route::get('pages/comments/{id}','PageController@comments');
    Route::post('pages/{id}/save', 'PageController@save');
    Route::post('pages/{id}/top', 'PageController@top');
    Route::post('pages/{id}/tag', 'PageController@tag');
    Route::resource('pages', 'PageController');
});
