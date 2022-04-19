<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSkipActions extends Model
{
    use HasFactory;

    protected $table = 'task_skip_actions';

    protected $fillable = [
        'task_id',
        'task_user',
        'task_user_url',
        'task_user_img'        
    ];
}
