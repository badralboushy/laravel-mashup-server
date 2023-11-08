<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RestaurantsController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\HotelsController;
use App\Http\Controllers\CityAutoCompleteController;
use App\Http\Controllers\PhotoesController;
use App\Http\Controllers\WeatherTimeController;
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

//public routes .. like register .. login

Route::get('/testUnprotected', function () {
    return "Unprotected route";
});

// register should be public
Route::post('/register' , [AuthController::class,'register']);

Route::Post('/login',[AuthController::class,'login']);







// protected routes

Route::group(['middleware' => ['auth:sanctum']], function () {
    // here add routes to when the user should authorized...EX:logout


    // this api  for testing
    Route::get('/testprotected',function(){
        return  "protected route";
    });

    // logout api
    Route::post('logout',[AuthController::class , 'logout']);


// AutoCompletion Api
    Route::get('/cityAutoComplete/{query}',[CityAutoCompleteController::class,'index']);


// this will return the weather and time
    Route::get('/weathertime/{lat}/{lng}/{days}',[WeatherTimeController::class,'index']);
// restaurant api
    Route::get('/restaurants/love/{user_id}/{restaurant_id}',[RestaurantsController::class,'likeRestaurant']);
    Route::POST('/getLovedRestaurants/{user_id}',[RestaurantsController::class,'getLovedRestaurant']);

//liked hotels api
    Route::get('/hotels/love/{user_id}/{hotel_id}',[HotelsController::class,'likeHotel']);
    Route::POST('/getLovedHotels/{user_id}',[HotelsController::class,'getLovedHotel']);

// hotel
    Route::get('/hotels/{lat}/{lng}/{rad}',[HotelsController::class,'index']);

// recommend Location Restaurant
    Route::POST('/recommendlocationRestaurant/{user_id}',[RestaurantsController::class,'RecommendLocationRestaurant']);
// recommend Location Hotels
    Route::POST('/recommendlocationHotel/{user_id}',[HotelsController::class,'RecommendLocationHotel']);
// restaurants api
    Route::get('/restaurants/{lat}/{lng}/{rad}',[RestaurantsController::class,'index']);
// recommended Restaurants
    Route::POST('/recommendRestaurant/{user_id}/{restaurant_id}',[RestaurantsController::class,'RecommendRestaurant']);
// Recommended Hotels
    Route::POST('/recommendHotel/{user_id}/{hotel_id}',[HotelsController::class,'RecommendHotel']);





});
