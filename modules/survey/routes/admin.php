<?php

/**
* 问卷
*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Modules\Survey\Web'], function () {
    Route::get('surveys/items/table/{survey_id}', 'SurveyItemController@table');
    Route::resource('surveys/items', 'SurveyItemController');

    Route::get('surveys/table', 'SurveyController@table');
    Route::post('surveys/{id}/top', 'SurveyController@top');
    Route::post('surveys/{id}/tag', 'SurveyController@tag');
    Route::get('surveys/statistic/{survey_id}', 'SurveyController@statistic');
    Route::post('surveys/state', 'SurveyController@state');
    Route::get('surveys/sort', 'SurveyController@sort');
    Route::get('surveys/comments/{id}','SurveyController@comments');
    Route::resource('surveys', 'SurveyController');
});
