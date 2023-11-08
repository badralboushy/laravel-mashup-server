<?php

namespace App\Http\Controllers;
use App\Models\Last_Searched_City;
use App\Models\Loved_hotels;
use App\Models\User;
use App\Models\User_loved_hotels;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use \App\Http\Resources\HotelsResource ;
use Error;
use Exception;
use PhpParser\Node\Expr\Cast\String_;

class HotelsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $lat,$lng,$rad)
    {
        //TODO:pagination

        // $lng =-73.96178513145257 ;
        // $lat=40.68696023184665 ;
        // $rad= 10000;                 // meters
        try{

            // get access token
            $response = Http::asForm()->post('https://api.amadeus.com/v1/security/oauth2/token', [
                'client_id' => config('services.amadeus_API.key'),
                'client_secret' => config('services.amadeus_API.secret'),
                'grant_type' => 'client_credentials',
            ]);
            $token = $response['access_token'];

            // search for hotels
            $response = Http::withToken($token)->get('https://api.amadeus.com/v2/shopping/hotel-offers',[
                    'latitude'=> $lat,
                   'longitude'=> $lng,
                   'radius'=>$rad/1000 ,
                   'radiusUnit'=> 'KM',  // KM,MILE
                   'includeClosed'=>false ,
                   'bestRateOnly'=> false,
                   'view'=> 'FULL',
                   'sort'=> 'Distance',  // Distance , Price , NONE
                   'page%5Blimit%5D'=> 96,  // less than 96

        ]);
            $user_id = $request->header('user_id');
            $record = Last_Searched_City::query()->select()->where('user_id','=',$user_id)->first();

            if($request->hasHeader('search')){
                if(isset($record)){
                    Last_Searched_City::query()->where('user_id','=',$user_id)->update(['hotels'=>$response]);
                }else{
                    Last_Searched_City::query()->insert(['restaurants'=>null,
                        'user_id'=>$user_id,
                        'hotels'=>$response,
                        'location_restaurants'=>null,
                        'location_hotels'=>null]);
                }
            }else if ($request->hasHeader('location')){
                if(isset($record)){
                    Last_Searched_City::query()->where('user_id','=',$user_id)->update(['location_hotels'=>$response]);
                }else {
                    Last_Searched_City::query()->insert(['restaurants' => null,
                        'user_id' => $user_id,
                        'hotels' => null,
                        'location_restaurants' => null,
                        'location_hotels' => $response]);
                }
            };
        }catch(Exception $e){
            $result['data']=[] ;
            $result['meta']['code'] = 500;
            $result['meta']['status'] = 'Server Error';
            $result['meta']['Message'] = "couldn't connect to the API";
            $result['meta']['ERROR_MESSAGE'] =$e->getmessage();

            return response($result,400);
        }

       $result = new HotelsResource($response->json()) ;
        return response($result,200) ;

    }
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function RecommendHotel(Request $request,$user_id , $hotel_id){
        $baseurl = config('services.recommender_system.baseurl');

        if($request->hasHeader('search')){
            $hot_json = Last_Searched_City::query()->select(['hotels'])->where('user_id','=',"$user_id")->first();
            if (isset($hot_json->errors)){
                return response([],200) ;

            }
            $response = Http::contentType('text/plain')
                ->send('POST',"http://$baseurl/similarity/hotels/$hotel_id/",['body'=>$hot_json->hotels])->json();
            //eturn $response;
            $result = new HotelsResource($response);
            return response($result,200) ;

        }else{
            $hot_json = Last_Searched_City::query()->select(['location_hotels'])->where('user_id','=',"$user_id")->first();
            if (isset($hot_json->errors)){
                return response([],200) ;
            }
            $response = Http::contentType('text/plain')
                ->send('POST',"http://$baseurl/similarity/hotels/$hotel_id/",['body'=>$hot_json->location_hotels])->json();
            //return ($response->json());
            $result = new HotelsResource($response);
            return response($result,200) ;
        }
    }
    public function RecommendLocationHotel($user_id){
        // get location hotels
        $result['data']=[];
        return response($result,200) ;
        $locationHotels = Last_Searched_City::query()->select(['hotels'])->where('user_id','=',"$user_id")->first();
        $locationHotels=json_decode($locationHotels->hotels);
        $user = User::find($user_id);
        $loved_hotels= $user->loved_hotels;
        $hotels = ['locationHotels'=>[],'lovedHotels'=>[]];



        $NUM = 0;
        foreach ($locationHotels->data as $hotel){
            $hotels['locationHotels'][$NUM] = $hotel ;
            $NUM++;
        }
        foreach($loved_hotels as $lvhotel){
           $hotels['lovedHotels'][$NUM] = json_decode($lvhotel->hotel) ;
           $NUM++;
         //   return json_decode($lvhotel->hotel);
        }
        return $hotels;


    }


    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     **/
    public function likeHotel(Request $request,$user_id,$hotel_id){
        // let's see if he is already in loved restaurant table.
        try{
            $hotel =Loved_hotels::find($hotel_id);
            if (!$hotel){
                // we need to add the hotel to the loved hotels table.
                // the hotel is saved in location hotels
                if($request->hasHeader('location')) {
                    $hotels = Last_Searched_City::query()->select(['location_restaurants'])->where('user_id', '=', $user_id)->first();
                    $hotels =json_decode($hotels->location_restaurants)->data;
                    // find the hotel the user liked
                    foreach( $hotels as $hotel){
                        if($hotel->hotel->hotelId == "$hotel_id"){
                            $result = $hotel;
                            // return $result;
                            Loved_hotels::query()->insert(['id'=>$hotel_id,'hotel'=>json_encode($result)]);
                            break ;
                        }
                    }

                    // the restaurant is saved in restaurant not location_restaurant;
                }elseif ($request->hasHeader('search')){
                    $hotels =Last_Searched_City::query()->select(['hotels'])->where('user_id','=',$user_id)->first();
                    $hotels =json_decode($hotels->hotels)->data;
                    // find the hotel the user liked
                    foreach( $hotels as $hotel){

                        if($hotel->hotel->hotelId == "$hotel_id"){
                            $result = $hotel;
                            // return $result;
                            Loved_hotels::query()->insert(['id'=>$hotel_id,'hotel'=>json_encode($result)]);
                            break ;
                        }
                    }
                }
                // restaurant id and user id to the pivot table.
                // but before that i need to check it is already in there
                $Exist = User_loved_hotels::query()->select()->where([
                    ['user_id','=',$user_id],
                    ['hotel_id','=',$hotel_id]
                ])->first();
                if(!$Exist){
                    User_loved_hotels::query()->insert(['user_id'=>$user_id,'hotel_id'=>$hotel_id]);
                }
                return response('done' ,200);
            }else {
                // we need to add that the user like this restaurant in pivot table.
                $Exist = User_loved_hotels::query()->select()->where([
                    ['user_id', '=', $user_id],
                    ['hotel_id', '=', $hotel_id]
                ])->first();
                if (!$Exist) {
                    User_loved_hotels::query()->insert(['user_id' => $user_id, 'hotel_id' => $hotel_id]);
                }
                return response('done', 200);
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

    public function getLovedHotel($user_id){
        $Hotels['data']=[] ;
        $user = User::find($user_id);
        $loved_hotels= $user->loved_hotels;
        if(!isset($loved_hotels[0]->hotel)){
            return response('No Hotels in the Wishlist',200) ;
        }
        foreach($loved_hotels as $lvhotel){
            array_push($Hotels['data'],json_decode($lvhotel->hotel));
        }
        $Result = [] ;
        foreach ($Hotels['data'] as $key => $value) {

            $Result['data'][$key]['name']= $value->hotel->name ?? 'NONE';
            $Result['data'][$key]['description'] = $value->hotel->description->text ?? 'NONE';
            $Result['data'][$key]['rating'] = $value->hotel->rating ?? 'NONE';
            $Result['data'][$key]['latitude'] =$value->hotel->latitude  ?? 'NONE';
            $Result['data'][$key]['longitude'] = $value->hotel->longitude ?? 'NONE';
            $Result['data'][$key]['photo'] =$value->hotel->media[0]->uri ?? 'NONE';
            $Result['data'][$key]['cityName'] = $value->hotel->address->cityName ?? 'NONE';
            $Result['data'][$key]['countryCode'] = $value->hotel->address->countryCode ?? 'NONE';
            $Result['data'][$key]['address'] = $value->hotel->address->lines ?? 'NONE';
            $Result['data'][$key]['location_id'] = $value->hotel->hotelId?? 'NONE';

            $Result['data'][$key]['phone'] = $value->hotel->contact->phone ?? 'NONE';
            $Result['data'][$key]['email'] =$value->hotel->contact->email ?? 'NONE';

        }
        $Result['meta']['code'] = 200;
        $Result['meta']['status'] = 'OK';
        $Result['meta']['Message'] = 'API connected succefully';

        // dd($Result);
        return $Result;

    }


}
