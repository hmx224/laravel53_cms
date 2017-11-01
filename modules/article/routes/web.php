<?php

/**
* 文章
*/
Route::group(['middleware' => 'web', 'namespace' => 'Modules\Article\Web'], function () {
    Route::get('article/index.html', 'ArticleController@lists');
    Route::get('article/detail-{id}.html', 'ArticleController@show');
    Route::get('article/{slug}.html', 'ArticleController@slug');
});
