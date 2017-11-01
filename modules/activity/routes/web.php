<?php

/**
* 活动
*/
Route::group(['middleware' => 'web', 'namespace' => 'Modules\Activity\Web'], function () {
    Route::get('activity/index.html', 'ActivityController@lists');
    Route::get('activity/detail-{id}.html', 'ActivityController@show');
    Route::get('activity/{slug}.html', 'ActivityController@slug');
});
