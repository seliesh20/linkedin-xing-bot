<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCookies extends Model
{
    use HasFactory;

    protected $table = 'task_cookies';

    protected $fillable = [
        'task_type',
        'login_email',
        'login_password',
        'cookie_string',
    ];
}
