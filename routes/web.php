<?php

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
   // return $router->app->version();
       return \Illuminate\Support\Str::random(32);
});

$router->get('/test',"Apicontrol@testing");

$router->group(["prefix"=>"api"], function() use ($router) {
    $router->post("signup",'Apicontrol@signup');
    $router->post("signin",'Apicontrol@signin');

    $router->post("getcategory","Apicontrol@getcategory");
    $router->post("postjob","Apicontrol@postjob");

    $router->post("searchajob","Apicontrol@searchforajob");

    $router->post("applytojob","Apicontrol@applytojob");
});

