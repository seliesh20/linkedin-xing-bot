<?php

namespace App\Http\Controllers;

use App\Models\TaskActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\Tasks;
use App\Models\TaskCrons;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;

class TaskController extends Controller
{
    //
    public function getTasks()
    {
        return DataTables::of(Tasks::query())
            ->addColumn("actions", function($task){
                return "<a href='".route('task.view', ['task_id' => $task->id])."'><i class='fa fa-eye'><i></a>";
            })
            ->addColumn("status", function($task){
                return $task->status->status_name;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
    
    public function create()
    {
        return view('task_create_form');
    }

    public function save(Request $request)
    {                
        $request->validate([
            'task_name' => 'required|unique:tasks|max:100',
            'task_type' => 'required',
            'login_email' => 'required|email',
            'login_password' => 'required',
            'search_query' => 'required',
            'message_male' => 'required|max:200',
            'message_female' => 'required|max:200',
            'message_undetect' => 'required|max:200',
            'request_delay' => 'required|numeric',
            'max_request' => 'required|numeric'
        ]);

        //Insert into Task table
        $row = $request->all();
        $row['task_status_id'] = 1;  
        //Adding Encryption
        $row["login_password"] = Crypt::encryptString($row["login_password"]);
        $task = Tasks::create($row);
        //Create Cron Record
        TaskCrons::create([
            'task_id' => $task->id,
            'type' => 'request',
            'start_time' => date("Y-m-d H:i:s"),
            'end_time' => date("Y-m-d H:i:s"),
            'status' => 0
        ]);
        TaskCrons::create([
            'task_id' => $task->id,
            'type' => 'withdraw',
            'start_time' => date("Y-m-d H:i:s"),
            'end_time' => date("Y-m-d H:i:s"),
            'status' => 0
        ]);
        return redirect('/home');        
    }

    public function view($task_id)
    {                
        return view('task_view',[
            'task' => Tasks::find($task_id),
            'task_actions' => TaskActions::where("task_id", $task_id)->get(),
            
        ]);
    }
}
