<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');

    $router->group(['prefix' => 'user', 'middleware' => 'auth'], function () use ($router) {
        $router->get('/', 'UserController@index');
    });

    $router->group(['prefix' => 'robot', 'middleware' => 'auth'], function () use ($router) {
        $router->get('/', 'RobotController@index');
        $router->get('/{id}', 'RobotController@show');
        $router->post('/', 'RobotController@store');
        $router->post('/uploadSpreadsheet', 'RobotController@storeBySpreadsheet');
        $router->patch('/{id}', 'RobotController@update');
        $router->delete('/{id}', 'RobotController@destroy');
    });

    $router->group(['prefix' => 'battle', 'middleware' => 'auth'], function () use ($router) {
        $router->post('/fight', 'BattleController@fight');
        $router->get('/results', 'BattleController@results');
        $router->get('/leaderboard', 'BattleController@leaderboard');
    });
});
