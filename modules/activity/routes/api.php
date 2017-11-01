<?php

/**
* 活动
*/
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    $api->group(['namespace' => 'Modules\Activity\Api'], function ($api) {
        $api->get('activities', 'ActivityController@lists');
        $api->get('activities/search', 'ActivityController@search');
        $api->get('activities/info', 'ActivityController@info');
        $api->get('activities/detail', 'ActivityController@detail');
        $api->get('activities/share', 'ActivityController@share');
        $api->get('activities/commit', 'ActivityController@commit');
    });
});