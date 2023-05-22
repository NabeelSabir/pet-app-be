<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{

	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Samples';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'type', 'size', 'breed', 'image', 'code', 'length', 'weight', 'length_unit', 'weight_unit'
    ];


    /**
     * Apply School ID condition.
     *
     * @param  [Builder]  query
     * @param  [integer]  code
     * @return relation
     *
     * @throws \Exception
     */
    public function scopeWhereCode($query,$code)
    {
        $query->where('code',$code);
    }

    public function SampleUsers()
    {
        return $this->hasMany('App\Models\SampleUser','Sample_id','id');
    }
}
