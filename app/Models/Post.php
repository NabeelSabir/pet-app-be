<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'posts';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'Sample_id', 'text'
    ];

    public function Sample()
    {
        return $this->hasOne('App\Models\Sample','id','Sample_id');
    }

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }

    public function attachments()
    {
        return $this->hasMany('App\Models\PostFiles','post_id','id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment','post_id','id');
    }

    public function likes()
    {
        return $this->hasMany('App\Models\Like','post_id','id');
    }
}
