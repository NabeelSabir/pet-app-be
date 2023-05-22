<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'device_tokens';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'value'
    ];

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }
}
