<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'likes';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'post_id'
    ];

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }

    public function post()
    {
        return $this->hasOne('App\Models\Post','id','post_id');
    }
}
