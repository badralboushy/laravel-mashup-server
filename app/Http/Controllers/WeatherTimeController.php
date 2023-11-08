<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use \App\Http\Resources\WeatherTimeResource;

class WeatherTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($lat,$lng,$days)
    {
        // $lng =-73.96178513145257 ;
        // $lat=40.68696023184665 ;
        // $days = '3';

        try{
            // here we call the api of places to find resturents

            $response =Http::withHeaders([
                'x-rapidapi-key' => config('services.weather_time.x-rapidapi-key'),
                'x-rapidapi-host' => config('services.weather_time.x-rapidapi-host'),
            ])->get('https://weatherapi-com.p.rapidapi.com/forecast.json', [
                'q' => "$lat, $lng",
                'days' => $days
            ]);
            $result = new WeatherTimeResource($response->json());
         //  dd($result->toJson());
            return response($result,200) ;
        }catch(Exception $e){
             $result['data']=[] ;
            $result['meta']['code'] = 500;
            $result['meta']['status'] = 'Server Error';
            $result['meta']['Message'] = "couldn't connect to the API";
            $result['meta']['ERROR_MESSAGE'] =$e->getmessage();
            return response($result,400);
        }



    }

}
