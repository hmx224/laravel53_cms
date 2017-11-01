<?php

/**
* 专题
*/
Route::group(['middleware' => 'web', 'namespace' => 'Modules\Special\Web'], function () {
    Route::get('specials/index.html', 'SpecialController@lists');
    Route::get('specials/detail-{id}.html', 'SpecialController@show');
    Route::get('specials/{slug}.html', 'SpecialController@slug');
});
