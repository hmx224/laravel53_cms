<?php

/**
* 问答
*/
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    $api->group(['namespace' => 'Modules\Question\Api'], function ($api) {
        $api->get('questions', 'QuestionController@lists');
        $api->get('questions/search', 'QuestionController@search');
        $api->get('questions/info', 'QuestionController@info');
        $api->get('questions/detail', 'QuestionController@detail');
        $api->get('questions/share', 'QuestionController@share');
    });
});