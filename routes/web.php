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
