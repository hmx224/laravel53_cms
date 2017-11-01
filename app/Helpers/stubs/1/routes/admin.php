<?php

/**
* __module_title__
*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Modules\__module_name__\Web'], function () {
    Route::get('__plural__/table', '__controller__@table');
    Route::post('__plural__/state', '__controller__@state');
    Route::get('__plural__/sort', '__controller__@sort');
    Route::get('__plural__/comments/{id}','__controller__@comments');
    Route::post('__plural__/{id}/save', '__controller__@save');
    Route::post('__plural__/{id}/top', '__controller__@top');
    Route::post('__plural__/{id}/tag', '__controller__@tag');
    Route::resource('__plural__', '__controller__');
});
