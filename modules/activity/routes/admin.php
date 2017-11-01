<?php

/**
* 活动
*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Modules\Activity\Web'], function () {
    Route::get('activities/table', 'ActivityController@table');
    Route::post('activities/state', 'ActivityController@state');
    Route::get('activities/sort', 'ActivityController@sort');
    Route::get('activities/comments/{id}','ActivityController@comments');
    Route::get('activities/categories', 'ActivityController@categories');
    Route::post('activities/{id}/save', 'ActivityController@save');
    Route::post('activities/{id}/top', 'ActivityController@top');
    Route::post('activities/{id}/tag', 'ActivityController@tag');
    Route::resource('activities', 'ActivityController');

    Route::get('activities/data/table/{activity_id}', 'ActivityDataController@table');
    Route::resource('activities/data', 'ActivityDataController');
});
