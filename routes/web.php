<?php
use Illuminate\Http\Request;

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/init', 'ServerController@initPanel');
$router->get('/cpu/percentage', 'ServerController@getCpuPercentage');
$router->get('/ram', 'ServerController@getRamData');
$router->get('/raid', 'ServerController@getRaidStatus');
