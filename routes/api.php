<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::prefix('v1')->namespace('App\Http\Controllers\Api')->group(function(){  //tem que passar o namespace completo nas versões atuais do laravel
    // Route::prefix('real-states')->name('real_states.')->group(function(){
    //     //Route::get('/', 'RealStateController@index')->name('index'); // /api/vi/real-states/
    //     Route::resource('/', 'RealStateController');
    // });


    Route::post('/login', 'Auth\\JwtController@login')->name('login');
    Route::post('/logout', 'Auth\\JwtController@logout')->name('logout');
    Route::post('/refresh', 'Auth\\JwtController@refresh')->name('refresh');

    Route::get('/search', 'RealStateSearchController@index')->name('search');
    Route::get('/search/{id}', 'RealStateSearchController@show')->name('search_single');

    Route::group(['middleware' => ['jwt.auth']], function() {
        Route::name('real_states.')->group(function(){
            //Route::get('/', 'RealStateController@index')->name('index'); // /api/vi/real-states/
            Route::resource('real-states', 'RealStateController'); //removendo o prefix e colocando no lugar da barra para que o laravel identifique o id depois da barra
        });

        Route::name('users.')->group(function(){
            Route::resource('users', 'UserController'); //removendo o prefix e colocando no lugar da barra para que o laravel identifique o id depois da barra
        });

        Route::name('categories.')->group(function(){
            Route::get('categories/{id}/real-states', 'CategoryController@realState');
            Route::resource('categories', 'CategoryController'); //removendo o prefix e colocando no lugar da barra para que o laravel identifique o id depois da barra
        });

        Route::name('photos.')->prefix('photos')->group(function(){
            Route::delete('/{id}', 'RealStatePhotoController@remove')->name('delete');
            Route::put('/set-thumb/{photoId}/{realStateId}', 'RealStatePhotoController@setThumb')->name('setThumb');
        });
    });

});
