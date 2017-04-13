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

$app->group(['prefix' => 'ldap', 'as' => 'ldap::'], function () use ($app) {
    $app->get('search', ['as' => 'search', 'uses' => 'LdapController@search']);
});

$app->group(['prefix' => 'auth', 'as' => 'auth::'], function () use ($app) {
    $app->get('radius', ['as' => 'auth', 'uses' => 'AuthController@authRadius']);
    $app->post('radius', ['as' => 'auth', 'uses' => 'AuthController@authRadius']);
});