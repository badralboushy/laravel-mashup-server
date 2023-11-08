<?php

namespace App\Http\Resources;

use Exception;
use GrahamCampbell\ResultType\Result;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Http\Resources\Json\JsonResource;

use function PHPSTORM_META\type;

class HotelsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

     // name , description , photo , rating , lat,lng , cityname, countrycode , phone , email


    public function toArray($request)
    {
        $Result = [] ;
        //dd($this->resource['data']);
        if ( isset($this->resource['errors'][0]['status'])){
          $Result['data']=[] ;
          $Result['meta']['code'] = -1;
          $Result['meta']['status'] = 'Server Error';
          $Result['meta']['Message'] = "There is no record found";
          return $Result;
        }
        foreach ($this->resource['data'] as $key => $value) {

                $Result['data'][$key]['name']= $value['hotel']['name'] ?? 'NONE';
                $Result['data'][$key]['description'] = $value['hotel']['description']['text'] ?? 'NONE';
                $Result['data'][$key]['rating'] = $value['hotel']['rating']  ?? 'NONE';
                $Result['data'][$key]['latitude'] =$value['hotel']['latitude']  ?? 'NONE';
                $Result['data'][$key]['longitude'] = $value['hotel']['longitude'] ?? 'NONE';
                $Result['data'][$key]['photo'] =$value['hotel']['media'][0]['uri'] ?? 'NONE';
                $Result['data'][$key]['cityName'] = $value['hotel']['address']['cityName'] ?? 'NONE';
                $Result['data'][$key]['countryCode'] = $value['hotel']['address']['countryCode'] ?? 'NONE';
                $Result['data'][$key]['address'] = $value['hotel']['address']['lines'] ?? 'NONE';
///data[0].hotel.hotelId
                $Result['data'][$key]['location_id'] = $value['hotel']['hotelId']?? 'NONE';

            $Result['data'][$key]['phone'] = $value['hotel']['contact']['phone'] ?? 'NONE';
                $Result['data'][$key]['email'] =$value['hotel']['contact']['email'] ?? 'NONE';

        }
        $Result['meta']['code'] = 200;
        $Result['meta']['status'] = 'OK';
        $Result['meta']['Message'] = 'API connected succefully';

       // dd($Result);
        return $Result;


    }
}
