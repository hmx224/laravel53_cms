<?php

/**
* 文章
*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Modules\Article\Web'], function () {
    Route::get('articles/table', 'ArticleController@table');
    Route::post('articles/state', 'ArticleController@state');
    Route::get('articles/sort', 'ArticleController@sort');
    Route::get('articles/comments/{id}','ArticleController@comments');
    Route::get('articles/categories', 'ArticleController@categories');
    Route::post('articles/{id}/save', 'ArticleController@save');
    Route::post('articles/{id}/top', 'ArticleController@top');
    Route::post('articles/{id}/tag', 'ArticleController@tag');
    Route::resource('articles', 'ArticleController');
});
