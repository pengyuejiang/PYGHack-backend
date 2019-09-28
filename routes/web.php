<?php

$router->group(['prefix' => 'passport'], function ($router) {
    $router->post('register', 'PassportController@register');

    $router->post('login', 'PassportController@login');

    $router->put('{id}/credentials', 'PassportController@putCredentials');

    $router->get('ping', 'PassportController@ping');

    $router->group(['middleware' => ['auth']], function ($router) {
        $router->put('', 'PassportController@putBatch');

        $router->put('{id}', 'PassportController@put');

        $router->delete('', 'PassportController@deleteBatch');

        $router->delete('{id}', 'PassportController@delete');

        $router->delete('{id}/forget', 'PassportController@forget');

        $router->get('info', 'PassportController@info');

        $router->get('', 'PassportController@index');
    });
});

$router->group(['prefix' => 'send'], function ($router) {
    $router->post('validation', 'SendController@sendValidation');
});

$router->group(['prefix' => 'coupon', 'middleware' => ['auth']], function ($router) {
    $router->post('register', 'CouponController@register');

    $router->put('consume/{sponsor_id}', 'CouponController@consume');

    $router->delete('{id}', 'CouponController@delete');

    $router->get('{id}', 'CouponController@view');

    $router->put('{id}', 'CouponController@put');

    $router->get('', 'CouponController@index');
});

$router->group(['prefix' => 'survey', 'middleware' => ['auth']], function ($router) {
    $router->group(['prefix' => 'template'], function ($router) {
        $router->post('register', 'SurveyTemplateController@register');

        $router->delete('{id}', 'SurveyTemplateController@delete');

        $router->get('{id}', 'SurveyTemplateController@view');

        $router->put('{id}', 'SurveyTemplateController@put');

        $router->get('', 'SurveyTemplateController@index');
    });
});

$router->get('test', 'SurveyTemplateController@test');
