<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResturentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

     // name , description , photo , rating , lat,lng , cityname, countrycode , phone , email ,address , cuisine
    public function toArray($request)
    {
        $Result = [] ;
        //print_r("hello");
        //print($this['data']);

            foreach ($this['data'] as $key => $value) {
                //return gettype(floatval($value['latitude']));
                $Result['data'][$key]['name']= $value['name'] ?? 'NONE';
                $Result['data'][$key]['description'] = $value['description']?? 'NONE';
                $Result['data'][$key]['rating'] = $value['rating']  ?? 'NONE';
                $Result['data'][$key]['latitude'] =$value['latitude']  ?? 'NONE';
                $Result['data'][$key]['longitude'] =$value['longitude'] ?? 'NONE';

                $Result['data'][$key]['location_id'] = $value['location_id'] ?? 'NONE';
                $Result['data'][$key]['phone'] = $value['phone'] ?? 'NONE';
                $Result['data'][$key]['open_now'] =$value['open_now_text'] ?? 'NONE';
                $Result['data'][$key]['location'] =$value['location_string'] ?? 'NONE';
                $Result['data'][$key]['website'] =$value['website'] ?? 'NONE';
                $Result['data'][$key]['address'] =$value['address'] ?? 'NONE';
                $Result['data'][$key]['cuisine'] =$value['cuisine'] ?? 'NONE';
                $Result['data'][$key]['photo']  = $value['photo']['images']['medium']['url']??'NONE';
            }
            $Result['meta']['code'] = 200;
            $Result['meta']['status'] = 'OK';
            $Result['meta']['Message'] = 'API connected succefully';

           // dd($Result);
            //data[13].photo.images.large.url

        return $Result;
    }
}
