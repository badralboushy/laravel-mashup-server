<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CityAutoCompleteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

     // cityname , cityCountry , longitude and latitude
    public function toArray($request)
    {
        $Result = [] ; 
      //  dd($this);

            
        foreach ($this->resource['data'] as $key => $value) {
    
                $Result['data'][$key]['cityName']= $value['address']['cityName'] ?? 'NONE';
                $Result['data'][$key]['detailedName']= $value['detailedName'] ?? 'NONE';
                $Result['data'][$key]['countryName'] = $value['address']['countryName'] ?? 'NONE';
                $Result['data'][$key]['latitude'] =$value['geoCode']['latitude']  ?? 'NONE';
                $Result['data'][$key]['longitude'] = $value['geoCode']['longitude'] ?? 'NONE';
               
        }
        $Result['meta']['code'] = 200;
        $Result['meta']['status'] = 'OK';
        $Result['meta']['Message'] = 'API connected succefully';
        
       // dd($Result);
        return $Result;

    }
}
