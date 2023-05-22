<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

// Constants
use App\Constants\General;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function createUser($username, $email, $password)
    {
        return self::create([
                'username' => $username,
                'email' => $email,
                'password' => bcrypt($password)
            ]);
    }

    public static function findUserByEmail($email)
    {
        return self::where('email', $email)->where('is_deleted', General::FALSE)->first();
    }

    public static function findUserByUsername($username)
    {
        return self::where('username', $username)->where('is_deleted', General::FALSE)->first();
    }
    
    public function deviceTokens()
    {
        return $this->hasMany('App\Models\DeviceToken','user_id','id');
    }
}
