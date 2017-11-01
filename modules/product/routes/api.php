<?php

/**
* 商品
*/
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    $api->group(['namespace' => 'Modules\Product\Api'], function ($api) {
        $api->get('products', 'ProductController@lists');
        $api->get('products/search', 'ProductController@search');
        $api->get('products/info', 'ProductController@info');
        $api->get('products/detail', 'ProductController@detail');
        $api->get('products/share', 'ProductController@share');
    });
});