<?php

/**
* __module_title__
*/
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    $api->group(['namespace' => 'Modules\__module_name__\Api'], function ($api) {
        $api->get('__plural__', '__controller__@lists');
        $api->get('__plural__/search', '__controller__@search');
        $api->get('__plural__/info', '__controller__@info');
        $api->get('__plural__/detail', '__controller__@detail');
        $api->get('__plural__/share', '__controller__@share');
    });
});