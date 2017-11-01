<?php

/**
* 文章
*/
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    $api->group(['namespace' => 'Modules\Article\Api'], function ($api) {
        $api->get('articles', 'ArticleController@lists');
        $api->get('articles/search', 'ArticleController@search');
        $api->get('articles/info', 'ArticleController@info');
        $api->get('articles/detail', 'ArticleController@detail');
        $api->get('articles/share', 'ArticleController@share');
    });
});