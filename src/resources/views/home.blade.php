@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="col-md-2 float-left">{{ config('app.name') }}</div>                
                    <div class="text-right col-md-2 float-right"><a class="btn btn-primary" href="{{route('task.create')}}">Add Task</a></div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered datatable-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <td>Task Name</td>
                                    <td>Task Type</td>
                                    <td>Status</td>
                                    <td>Created on</td>
                                    <td>Actions</td>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script type="application/javascript" defer`>
$(function(){
    $('table.datatable-table').DataTable({
        processing: true,
        serverSide: true,
        responsive:true,        
        ajax:{
            url:"{{ route('task.list') }}",
            type:"post",
            data:function(d){            
                d._token=$("input[name=_token]").val();
            },
        },
        columns:[
            {data:"task_name", name:"task_name"},
            {data:"task_type", name:"task_type", render:function(data, type, row, meta){
                return data = "L"?'LinkedIn':'Xing';
            }},
            {data:"status", name:"status"},
            {data:"created_at", name:"created_at", render:function(data, type, row, meta){
                return moment(data).format("DD/MM/YYY hh:mm:ss A");
            }},
            {data:"actions", name:"actions"}
        ]
    }); 
});
</script>
@endsection
