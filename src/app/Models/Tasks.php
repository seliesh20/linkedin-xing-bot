<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'task_name',
        'task_type',
        'search_query',
        'login_email',
        'login_password',
        'message_male',
        'message_female',
        'message_undetect',
        'request_delay',
        'max_request',
        'task_status_id'
    ];

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }
}
