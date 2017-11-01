<?php

/**
* 页面
*/
Route::group(['middleware' => 'web', 'namespace' => 'Modules\Page\Web'], function () {
    Route::get('page/index.html', 'PageController@lists');
    Route::get('page/detail-{id}.html', 'PageController@show');
    Route::get('page/{slug}.html', 'PageController@slug');
});
