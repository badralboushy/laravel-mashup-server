<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_loved_hotels extends Model
{
    use HasFactory;
    protected $table = 'user_loved_hotels';
    protected $fillable = ['hotel_id','user_id' , 'created_at','updated_at'];
    protected $hidden = ['created_at','updated_at'];
    public $timestamps = true ;
}
