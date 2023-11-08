<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Resources\CityAutoCompleteResource;

use Exception;

class CityAutoCompleteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($query)
    {

        try{
            
            // get access token 
            $response = Http::asForm()->post('https://api.amadeus.com/v1/security/oauth2/token', [
                'client_id' => config('services.amadeus_API.key'),
                'client_secret' => config('services.amadeus_API.secret'),
                'grant_type' => 'client_credentials',
            ]);
            $token = $response['access_token'];
            // dd($response);
            //dd($token);
    
            // search for cities
            $response = Http::withToken($token)->get('https://api.amadeus.com/v1/reference-data/locations',[
                'subType'=> 'CITY',
                'keyword'=> $query,  
                'sort'=>'analytics.travelers.score' ,  
                'view'=> 'FULL',         
        ]);

        }catch(Exception $e){
            $result['data']=[] ; 
            $result['meta']['code'] = 500;
            $result['meta']['status'] = 'Server Error';
            $result['meta']['Message'] = "couldn't connect to the API";
            $result['meta']['ERROR_MESSAGE'] =$e->getmessage();
         
            return response( $result , 400);
        }

        $result = new CityAutoCompleteResource($response->json()) ; 
        return response($result,200) ;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
