<?php

/**
* __module_title__
*/
Route::group(['middleware' => 'web', 'namespace' => 'Modules\__module_name__\Web'], function () {
    Route::get('__module_path__/index.html', '__controller__@lists');
    Route::get('__module_path__/detail-{id}.html', '__controller__@show');
    Route::get('__module_path__/{slug}.html', '__controller__@slug');
});
