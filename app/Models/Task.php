<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * All data about task
 */
class Task extends Model {
    protected $table = 'tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'priority',
        'project_id',
    ];

    protected $casts = [
        'name'           =>  'string',
        'priority'       =>  'integer',
        'project_id'     =>  'integer',
    ];
}
