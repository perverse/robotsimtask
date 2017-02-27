<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api'], function()
{
    Route::group(['prefix' => 'shop'], function()
    {
        Route::post('/', ['uses' => 'ShopController@create', 'as' => 'api.shop.create']);

        Route::group(['prefix' => '{id}'], function()
        {
            Route::get('/', ['uses' => 'ShopController@find', 'as' => 'api.shop.find']);
            Route::delete('/', ['uses' => 'ShopController@destroy', 'as' => 'api.shop.destroy']);
            Route::post('/execute', ['uses' => 'ShopController@simulate', 'as' => 'api.shop.simulate']);

            Route::group(['prefix' => 'robot'], function(){
                Route::post('/', ['uses' => 'ShopRobotController@create', 'as' => 'api.shop.robot.create']);
                Route::put('/{rid}', ['uses' => 'ShopRobotController@update', 'as' => 'api.shop.robot.update']);
                Route::delete('/{rid}', ['uses' => 'ShopRobotController@destroy', 'as' => 'api.shop.robot.destroy']);
            });
        });
    });
});
