<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WeatherTimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $Result = [] ; 
        //  print_r("hello");
       //   dd($this->resource[]);
          $Result['data']['time'] = $this->resource['location']['localtime'];
          $Result['data']['name'] = $this->resource['location']['name'];
          $Result['data']['region'] = $this->resource['location']['region'];
          $Result['data']['country'] = $this->resource['location']['country'];
          $Result['data']['current_temp_c'] = $this->resource['current']['temp_c'];
          $Result['data']['current_weather_text'] = $this->resource['current']['condition']['text'];
          $Result['data']['current_weather_icon'] = $this->resource['current']['condition']['icon'];
        
        

  
              foreach ($this->resource['forecast']['forecastday'] as $key => $value) {
            
                  $Result['data'][$key]['date']= $value['date'] ?? 'NONE';
                  $Result['data'][$key]['maxtemp_c']= $value['day']['maxtemp_c'] ?? 'NONE';
                  $Result['data'][$key]['mintemp_c']= $value['day']['mintemp_c'] ?? 'NONE';
                  $Result['data'][$key]['avgtemp_c']= $value['day']['avgtemp_c'] ?? 'NONE';
                  $Result['data'][$key]['weather_text']= $value['day']['condition']['text'] ?? 'NONE';
                  $Result['data'][$key]['weather_icon']= $value['day']['condition']['icon'] ?? 'NONE';
                  
                
              }
              $Result['meta']['code'] = 200;
              $Result['meta']['status'] = 'OK';
              $Result['meta']['Message'] = 'API connected succefully';
  
           // dd($Result);
  
  
          return $Result;
       
    }
}
