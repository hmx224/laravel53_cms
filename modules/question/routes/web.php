<?php

/**
* 问答
*/
Route::group(['middleware' => 'web', 'namespace' => 'Modules\Question\Web'], function () {
    Route::get('question/index.html', 'QuestionController@lists');
    Route::get('question/detail-{id}.html', 'QuestionController@show');
    Route::get('question/{slug}.html', 'QuestionController@slug');
});
