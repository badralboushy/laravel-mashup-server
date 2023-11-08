<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loved_restaurants extends Model
{
    use HasFactory;
    protected $table = 'loved_restaurants';
    protected  $fillable = ['id','restaurant','created_at','updated_at'];
    protected $hidden = ['created_at','updated_at'];
    public $timestamps = true;

    ###################################### Begin relations ##############################

    public function users(){
        return $this->belongsToMany('App\Models\User','user_loved_restaurants','restaurant_id','user_id','id','id');
    }







    ###################################### End relations ##############################


}
