<?php

/**
* 商品
*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Modules\Product\Web'], function () {
    Route::get('products/table', 'ProductController@table');
    Route::post('products/state', 'ProductController@state');
    Route::get('products/sort', 'ProductController@sort');
    Route::get('products/comments/{id}','ProductController@comments');
    Route::get('products/categories', 'ProductController@categories');
    Route::post('products/{id}/save', 'ProductController@save');
    Route::post('products/{id}/top', 'ProductController@top');
    Route::post('products/{id}/tag', 'ProductController@tag');
    Route::resource('products', 'ProductController');
});
