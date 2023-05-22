<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationSetting extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_notification_settings';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'like', 'reply', 'mention', 'event', 'new_user' , 'post'
    ];

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }


    public static function findOrCreate($user_id)
    {
        $setting = self::where('user_id', $user_id)->first();

        if(empty($setting))
        {
            $setting = self::create([
                "user_id" => $user_id,
                "like" => 1,
                "reply" => 1,
                "mention" => 1,
                "event" => 1,
                "new_user" => 1,
            ]);
        }

        return $setting;
    }
}
