<?php

/**
* 媒资
*/
Route::group(['middleware' => 'web', 'namespace' => 'Modules\Video\Web'], function () {
    Route::get('videos/index.html', 'VideoController@lists');
    Route::get('videos/category-{id}.html', 'VideoController@category');
    Route::get('videos/detail-{id}.html', 'VideoController@show');
    Route::get('videos/{slug}.html', 'VideoController@slug');
});
