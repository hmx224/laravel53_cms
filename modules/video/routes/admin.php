<?php

/**
 * 媒资
 */
Route::group(['prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Modules\Video\Web'], function () {
    Route::get('videos', 'VideoController@show');
    Route::get('videos/table', 'VideoController@table');
    Route::post('videos/state', 'VideoController@state');
    Route::get('videos/sort', 'VideoController@sort');
    Route::get('videos/comments/{id}', 'VideoController@comments');
    Route::get('videos/categories', 'VideoController@categories');
    Route::post('videos/{id}/save', 'VideoController@save');
    Route::post('videos/{id}/top', 'VideoController@top');
    Route::post('videos/{id}/tag', 'VideoController@tag');

    Route::get('videos/filters/{state}', 'VideoController@filters');
    Route::get('videos/list', 'VideoController@list');

    Route::resource('videos', 'VideoController');
});
