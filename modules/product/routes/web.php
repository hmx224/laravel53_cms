<?php

/**
* 商品
*/
Route::group(['middleware' => 'web', 'namespace' => 'Modules\Product\Web'], function () {
    Route::get('product/index.html', 'ProductController@lists');
    Route::get('product/detail-{id}.html', 'ProductController@show');
    Route::get('product/{slug}.html', 'ProductController@slug');
});
