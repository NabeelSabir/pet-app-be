<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostFiles extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post_files';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id', 'file_path', 'type'
    ];
}
