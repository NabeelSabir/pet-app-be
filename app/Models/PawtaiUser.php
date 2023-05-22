<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleUser extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Sample_users';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'Sample_id', 'type'
    ];

    /**
     * Apply School ID condition.
     *
     * @param  [Builder]  query
     * @param  [integer]  user_id
     * @return relation
     *
     * @throws \Exception
     */
    public function scopeWhereUserId($query,$user_id)
    {
        $query->where('user_id',$user_id);
    }


    /**
     * Apply School ID condition.
     *
     * @param  [Builder]  query
     * @param  [integer]  Sample_id
     * @return relation
     *
     * @throws \Exception
     */
    public function scopeWhereSampleId($query,$Sample_id)
    {
        $query->where('Sample_id',$Sample_id);
    }
}
