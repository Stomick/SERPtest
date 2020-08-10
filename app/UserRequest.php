<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRequest extends Model
{
    protected $table = 'user_requests';
    protected $primaryKey = 'id';

    protected $fillable = [
        'post_key',
        "status",
        "task_id",
        "empty"
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];


}
