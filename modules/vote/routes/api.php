<?php

/**
* 问卷
*/
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    $api->group(['namespace' => 'Modules\Vote\Api'], function ($api) {
        $api->get('votes', 'VoteController@lists');
        $api->post('votes/create', 'VoteController@create');
        $api->get('votes/detail', 'VoteController@detail');
    });

});