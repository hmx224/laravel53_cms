<?php

/**
* 问答
*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Modules\Question\Web'], function () {
    Route::get('questions/table', 'QuestionController@table');
    Route::post('questions/state', 'QuestionController@state');
    Route::get('questions/sort', 'QuestionController@sort');
    Route::get('questions/comments/{id}','QuestionController@comments');
    Route::post('questions/{id}/save', 'QuestionController@save');
    Route::post('questions/{id}/top', 'QuestionController@top');
    Route::post('questions/{id}/tag', 'QuestionController@tag');
    Route::resource('questions', 'QuestionController');
});
