<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Sample_id', 'user_id', 'text', 'from_user_id', 'post_id', 'comment_id', 'type' , 'is_read'
    ];

    public function Sample()
    {
        return $this->hasOne('App\Models\Sample','id','Sample_id');
    }

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }

    public function fromUser()
    {
        return $this->hasOne('App\User','id','from_user_id');
    }

    public function post()
    {
        return $this->hasOne('App\Models\Post','id','post_id');
    }

    public function comment()
    {
        return $this->hasOne('App\Models\Comment','id','comment_id');
    }
}
