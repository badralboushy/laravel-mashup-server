<?php

namespace App\Http\Controllers;

use App\Models\Last_Searched_City;
use App\Models\Loved_restaurants;
use App\Models\User;
use App\Models\User_loved_restaurants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client ;
use \App\Http\Resources\ResturentsResource ;
use Facade\FlareClient\Http\Client as HttpClient;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\Request as Psr7Request;
use function MongoDB\BSON\toJSON;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class RestaurantsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$lat,$lng,$rad)
    {

        // $lng =-73.96178513145257 ;
        // $lat=40.68696023184665 ;
        // $rad= 10000;                 // meters
        try{
            // here we call the api of places to find resturents

            $response =Http::withHeaders([
                'x-rapidapi-key' => config('services.travel_advisor.x-rapidapi-key'),
                'x-rapidapi-host' => config('services.travel_advisor.x-rapidapi-host'),
            ])->get('https://travel-advisor.p.rapidapi.com/restaurants/list-by-latlng', [
                'latitude' => $lat,
                'longitude' => $lng,
                'limit' => '30',         // max is 30
                'currency' => 'USD',
                'distance' => $rad/1000, // max is 10000
                'open_now' => 'true',
                'lunit' => 'km',
                'lang' => 'en_US'
            ]);
            $user_id = $request->header('user_id');
            if($request->hasHeader('search')){
                $record = Last_Searched_City::query()->select()->where('user_id','=',$user_id)->first();
                if(isset($record)){
                    Last_Searched_City::query()->where('user_id','=',$user_id)->update(['restaurants'=>$response]);

                }else{
                  Last_Searched_City::query()->insert([
                      'user_id'=>$user_id,
                      'restaurants'=>$response,
                      'hotels'=>null,
                      'location_restaurants'=>null,
                      'location_hotels'=>null]);
              }
            }if($request->hasHeader('location')){
                $record = Last_Searched_City::query()->select()->where('user_id','=',$user_id)->first();
                if(isset($record)){
                    Last_Searched_City::query()->where('user_id','=',$user_id)->update(['location_restaurants'=>$response]);
                }else{

                    Last_Searched_City::query()->insert(['location_restaurants'=>$response,
                        'user_id'=>$user_id,
                        'hotels'=>null,
                        'restaurants'=>null,
                        'location_hotels'=>null]);
                }
            }


            $result = new ResturentsResource($response->json());

            return response($result,200) ;
        }catch(Exception $e){
             $result['data']=[] ;
            $result['meta']['code'] = 500;
            $result['meta']['status'] = 'Server Error';
            $result['meta']['Message'] = "couldn't connect to the API";
            $result['meta']['ERROR_MESSAGE'] =$e->getmessage();
            return response($result,400) ;
        }

    }

    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function RecommendRestaurant(Request $request,$user_id, $restaurant_id){
        $baseurl = config('services.recommender_system.baseurl');

        if($request->hasHeader('search')){
            $rest_json = Last_Searched_City::query()->select(['restaurants'])->where('user_id','=',"$user_id")->first();
          //  return $rest_json->restaurants;
            $response = Http::contentType('text/plain')
                ->send('POST',"http://$baseurl/similarity/restaurants/$restaurant_id/",['body'=>$rest_json->restaurants])->json();
            $result = new ResturentsResource($response);
            return response($result,200) ;

        }else{
            $rest_json = Last_Searched_City::query()->select(['location_restaurants'])->where('user_id','=',"$user_id")->first();
            $response = Http::contentType('text/plain')
                ->send('POST',"http://$baseurl/similarity/restaurants/$restaurant_id/",['body'=>$rest_json->location_restaurants])->json();
            //return ($response);
            $result = new ResturentsResource($response);
            return response($result,200) ;
        }
        //return $baseurl;

      //  return $response;http://badralboushy.pythonanywhere.com
    }


    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     **/
    public function likeRestaurant(Request $request,$user_id,$restaurant_id){
        // let's see if he is already in loved restaurant table.
        try{

            $restaurant =Loved_restaurants::find($restaurant_id);
            if (!$restaurant){
                // we need to add the restaurant to the loved restaurants table.
                // the restaurant is saved in location restaurants
                if($request->hasHeader('location')) {

                    $restaurants = Last_Searched_City::query()->select(['location_restaurants'])->where('user_id', '=', $user_id)->first();
                    $restaurants =json_decode($restaurants->location_restaurants)->data;
                    // find the restaurant the user liked
                    foreach( $restaurants as $restaurant){
                        if($restaurant->location_id == "$restaurant_id"){
                            $result = $restaurant ;
                            Loved_restaurants::query()->insert(['id'=>$restaurant_id,'restaurant'=>json_encode($result)]);
                            break ;
                        }
                    }
                    // the restaurant is saved in restaurant not location_restaurant;
                }elseif ($request->hasHeader('search')){

                    $restaurants =Last_Searched_City::query()->select(['restaurants'])->where('user_id','=',$user_id)->first();
                    $restaurants =json_decode($restaurants->restaurants)->data;
                    // find the restaurant the user liked
                    foreach( $restaurants as $restaurant){
                        if($restaurant->location_id == "$restaurant_id"){
                            $result = $restaurant ;
                            //  return $result;
                            Loved_restaurants::query()->insert(['id'=>$restaurant_id,'restaurant'=>json_encode($result)]);
                            break ;
                        }

                    }
                }
                // restaurant id and user id to the pivot table.
                $Exist = User_loved_restaurants::query()->select()->where([
                    ['user_id','=',$user_id],
                    ['restaurant_id','=',$restaurant_id]
                ])->first();
                if(!$Exist) {
                    User_loved_restaurants::query()->insert(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
                }
                return response('done' ,200);

            }else{
                // we need to add that the user like this restaurant in pivot table.
                $Exist = User_loved_restaurants::query()->select()->where([
                    ['user_id','=',$user_id],
                    ['restaurant_id','=',$restaurant_id]
                ])->first();
                if(!$Exist) {
                    User_loved_restaurants::query()->insert(['user_id' => $user_id, 'restaurant_id' => $restaurant_id]);
                }
                return response('done' ,200);

            }

        }catch (Exception $e){
            $result['data']=[] ;
            $result['meta']['code'] = 500;
            $result['meta']['status'] = 'Server Error';
            $result['meta']['Message'] = "couldn't connect to the API";
            $result['meta']['ERROR_MESSAGE'] =$e->getmessage();
            return response($result,400) ;
        }

    }


    public function RecommendLocationRestaurant($user_id){
        // get location hotels
        $locationRestaurants = Last_Searched_City::query()->select(['location_restaurants'])->where('user_id','=',"$user_id")->first();
        $locationRestaurants=json_decode($locationRestaurants->location_restaurants);
       /// return $locationRestaurants;

        $user = User::find($user_id);
        $loved_restaurants= $user->loved_restaurants;
        if(!isset($loved_restaurants[0]->restaurant)){
            return response('No Restaurants in the Wishlist',200) ;
        }
       // return $loved_restaurants;
        $restaurants = ['data'=>[]];
        $NUM = 0;
        foreach($loved_restaurants as $lvrestaurant){
            $restaurants['data'][$NUM] = json_decode($lvrestaurant->restaurant) ;
            $NUM++;
        }
        $separator = $NUM;
        foreach ($locationRestaurants->data as $locrestaurant){
            $restaurants['data'][$NUM] = $locrestaurant;
            $NUM++;
        }
        $baseurl = config('services.recommender_system.baseurl');
        $response = Http::contentType('text/plain')
            ->send('POST',"http://$baseurl/similarity/restaurants/location/$separator/",['body'=>json_encode($restaurants)])->json();
        $result = new ResturentsResource($response);
        return response($result,200) ;
    }
    public function getLovedRestaurant($user_id){
        $restaurants['data']=[] ;
        $user = User::find($user_id);
        $loved_restaurants= $user->loved_restaurants;
        if(!isset($loved_restaurants[0]->restaurant)){
            return response('No Restaurants in the Wishlist',200) ;
        }
        //return $loved_restaurants;
        foreach($loved_restaurants as $lvrestaurant){
            array_push($restaurants['data'],json_decode($lvrestaurant->restaurant));
        }
        $Result = [] ;
        foreach ($restaurants['data'] as $key => $value) {
            //return gettype(floatval($value['latitude']));
            $Result['data'][$key]['name']= $value->name ?? 'NONE';
            $Result['data'][$key]['description'] = $value->description?? 'NONE';
            $Result['data'][$key]['rating'] = $value->rating ?? 'NONE';
            $Result['data'][$key]['latitude'] =$value->latitude  ?? 'NONE';
            $Result['data'][$key]['longitude'] =$value->longitude ?? 'NONE';

            $Result['data'][$key]['location_id'] = $value->location_id ?? 'NONE';
            $Result['data'][$key]['phone'] = $value->phone ?? 'NONE';
            $Result['data'][$key]['open_now'] =$value->open_now_text ?? 'NONE';
            $Result['data'][$key]['location'] =$value->location_string ?? 'NONE';
            $Result['data'][$key]['website'] =$value->website ?? 'NONE';
            $Result['data'][$key]['address'] =$value->address ?? 'NONE';
            $Result['data'][$key]['cuisine'] =$value->cuisine ?? 'NONE';
            $Result['data'][$key]['photo']  = $value->photo->images->medium->url??'NONE';
        }
        $Result['meta']['code'] = 200;
        $Result['meta']['status'] = 'OK';
        $Result['meta']['Message'] = 'API connected succefully';
        return $Result;

    }
}
