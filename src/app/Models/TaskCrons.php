<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCrons extends Model
{
    use HasFactory;

    protected $table = 'task_crons';

    protected $fillable = [
        'task_id',
        'start_time',
        'end_time',
        'status'        
    ];

    public function task()
    {
        return $this->belongsTo(Tasks::class);
    }
}
