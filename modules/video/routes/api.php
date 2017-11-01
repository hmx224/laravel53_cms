<?php

/**
* 媒资
*/
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    $api->group(['namespace' => 'Modules\Video\Api'], function ($api) {
        $api->get('videos', 'VideoController@lists');
        $api->get('videos/search', 'VideoController@search');
        $api->get('videos/info', 'VideoController@info');
        $api->get('videos/detail', 'VideoController@detail');
        $api->get('videos/share', 'VideoController@share');
    });
});