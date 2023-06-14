<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * All data about project
 */
class Project extends Model {
    protected $table = 'projects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name'           =>  'string',
    ];
}
