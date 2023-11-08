<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\loved_restaurants;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    ######################### Begin Relations #######################


    public function last_searched_city(){
        return $this->hasOne('App\Models\Last_Searched_City','user_id');
    }
    public function loved_restaurants(){
        return $this->belongsToMany('App\Models\Loved_restaurants','user_loved_restaurants','user_id','restaurant_id','id','id');
    }

    public function loved_hotels(){
        return $this->belongsToMany('App\Models\Loved_hotels','user_loved_hotels','user_id','hotel_id','id','id');
    }

    ######################### End Relations #######################

}
