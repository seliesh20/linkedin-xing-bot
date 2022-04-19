@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="col-md-2 float-left">{{ __("View Task") }}</div>                
                    <div class="text-right col-md-2 float-right"></div>
                </div>
                <div class="card-body">
                    <div class="col-md-12">
                        <div class="col-md-4 float-left">
                            <strong>Task Name</strong></br/>
                            {{$task->task_name}}
                        </div>
                        <div class="col-md-4 float-left">
                            <strong>Task Type</strong></br/>
                            {{$task->task_type == LINKEDIN?'LinkedIn':'Xing'}}
                        </div>
                        <div class="col-md-4 float-left">
                            <strong>Search Query</strong></br/>
                            {{$task->search_query}}
                        </div>                        
                    </div>
                    <i class="clearfix"></i><br/>
                    <div class="col-md-12">
                        <div class="col-md-4 float-left">
                            <strong>Login Emain</strong></br/>
                            {{$task->login_email}}
                        </div>
                        <div class="col-md-4 float-left">
                            <strong>Login Password</strong></br/>
                            {{$task->login_password}}
                        </div>
                        <div class="col-md-4 float-left">
                            <strong>Maximum Requests</strong></br/>
                            {{$task->max_request}}
                        </div>                        
                    </div>
                    <i class="clearfix"></i><br/>
                    <div class="col-md-12">
                        <div class="col-md-4 float-left">
                            <strong>Request Delay(in seconds)</strong></br/>
                            {{$task->request_delay}}
                        </div>
                        <div class="col-md-4 float-left">
                            <strong>Task Status</strong></br/>
                            {{$task->status->status_name}}
                        </div>                                                
                    </div>
                    <i class="clearfix"></i><br/>
                    <div class="col-md-12">
                        <div class="col-md-4 float-left">
                            <strong>Message if gender male</strong><br/>
                            {{$task->message_male}}
                        </div>
                        <div class="col-md-4 float-left">
                            <strong>Message if gender female</strong><br/>
                            {{$task->message_female}}
                        </div>
                        <div class="col-md-4 float-left">
                            <strong>Message if gender undetected</strong><br/>
                            {{$task->message_undetect}}
                        </div>
                    </div>
                </div>
            </div> <br/>
            <div class="card">
                <div class="card-header">
                    <div class="col-md-2 float-left">{{ __("Task Actions") }}</div>                
                    <div class="text-right col-md-2 float-right"></div>
                </div>
                <div class="card-body"> 
                    <table id="task_action" class="table" width="100%">
                        <thead>
                            <tr>
                                <th style="width:50%">Task User</th>
                                <th style="width:10%">Status</th>
                                <th style="width:35%">Events</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($task_actions->count())
                                @foreach($task_actions as $action)                            
                                    <tr>
                                        <td>
                                            <span >
                                                @if(strlen(trim($action->task_user_img)))
                                                    <img class="rounded-circle" src="{{$action->task_user_img}}" width="50"/>
                                                @else
                                                    <i class="fa fa-3x rounded-circle fa-user"></i>            
                                                @endif
                                            </span>&nbsp;
                                            <a href="{{$action->task_user_url}}" target="_blank" style="font-size:16px;">{{$action->task_user}}</a>
                                        </td>
                                        <td>{{$action->task_run_status}}</td>
                                        <td>
                                            {{"Send on "}}{{date('d/m/Y h:i:s A', strtotime($action->created_at))}}<br/>
                                            @if($action->task_run_status == "withdrawn")
                                                {{"Withdrawn on "}}{{date('d/m/Y h:i:s A', strtotime($action->updated_at))}}<br/>
                                            @endif
                                        </td>
                                    </tr>                                    
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div> 
    </div> 
</div>          
@endsection