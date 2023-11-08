<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_loved_restaurants extends Model
{
    use HasFactory;
    protected $table = 'user_loved_restaurants';
    protected $fillable = ['restaurant_id','user_id' , 'created_at','updated_at'];
    protected $hidden = ['created_at','updated_at'];
    public $timestamps = true ;
}
