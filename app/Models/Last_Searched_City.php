<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Last_Searched_City extends Model
{
    use HasFactory;
    protected $table = 'last_searched_city';
    protected $fillable = ['user_id','restaurants','hotels','location_restaurants','location_hotels','created_at','updated_at'];
    protected $hidden= ['created_at','updated_at'];
    public $timestamps = true ;


    ######################### Begin Relations #######################

    public function user(){
        return $this->belongsTo('App\Models\User','user_id');
    }


    ######################### End Relations #######################

}
