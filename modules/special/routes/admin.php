<?php

/**
* 专题
*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Modules\Special\Web'], function () {
    Route::get('specials/table', 'SpecialController@table');
    Route::post('specials/state', 'SpecialController@state');
    Route::get('specials/sort', 'SpecialController@sort');
    Route::get('specials/comments/{id}','SpecialController@comments');
    Route::get('specials/categories', 'SpecialController@categories');
    Route::post('specials/{id}/save', 'SpecialController@save');
    Route::post('specials/{id}/top', 'SpecialController@top');
    Route::post('specials/{id}/tag', 'SpecialController@tag');
    Route::resource('specials', 'SpecialController');
});
