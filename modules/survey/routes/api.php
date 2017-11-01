<?php

/**
* 问卷
*/
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    $api->group(['namespace' => 'Modules\Survey\Api'], function ($api) {
        $api->get('surveys', 'SurveyController@lists');
        $api->post('surveys/submit', 'SurveyController@submit');
        $api->get('surveys/detail', 'SurveyController@detail');
    });

});