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

$app->group(['prefix' => 'fishcam', 'as' => 'fishcam::'], function () use ($app) {
    $app->get('stream', ['as' => 'stream', 'uses' => 'FishcamController@getStream']);
});

$app->group(['prefix' => 'radius', 'as' => 'radius::'], function () use ($app) {
    $app->get('auth', ['as' => 'auth', 'uses' => 'RadiusController@authenticate']);
});