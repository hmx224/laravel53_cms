<?php

/**
* 投票
*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Modules\Vote\Web'], function () {
    Route::get('votes/items/table/{vote_id}', 'VoteItemController@table');
    Route::resource('votes/items', 'VoteItemController');
    Route::get('votes/sort', 'VoteController@sort');
    Route::get('votes/table', 'VoteController@table');
    Route::get('votes/statistic/{vote_id}', 'VoteController@statistic');
    Route::post('votes/state', 'VoteController@state');
    Route::post('votes/{id}/top', 'VoteController@top');
    Route::post('votes/{id}/tag', 'VoteController@tag');
    Route::get('votes/comments/{id}','VoteController@comments');
    Route::resource('votes', 'VoteController');
});
