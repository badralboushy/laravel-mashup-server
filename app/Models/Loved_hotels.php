<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loved_hotels extends Model
{
    use HasFactory;
    protected $table = 'loved_hotels';
    protected $fillable= ['id','hotel','created_at','updated_at'];
    protected $hidden = ['created_at','updated_at'];
    public $timestamps = true;

    ################################ Begin relations ###############################

    public function user(){
        return $this->belongsToMany('App\Models\User','user_loved_hotels','hotel_id','user_id','id','id');
    }


    ################################ End relations ###############################
}
