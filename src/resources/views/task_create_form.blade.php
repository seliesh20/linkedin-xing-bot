@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="col-md-2 float-left">{{ __("Add Task") }}</div>
                    <div class="text-right col-md-2 float-right"></div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Please Use <code>{full_name}</code> in message field to replace client username
                        with.<br> <code>{first_name}</code> for client first name.<br> <code>{last_name}</code>
                        for client last name.
                    </div>
                    <form class="needs-validation {{$errors->any()?'was-validated':''}}" name="task_create_form" id="task-create-form" method="post" action="{{route('task.save')}}" novalidate>
                        <div class="col-md-2 float-left">&nbsp;</div>
                        <div class="col-md-5 float-left">
                            <div class="form-group">
                                {{Form::token()}}
                                {{ Form::label('task_name', 'Task Name') }}
                                {{ Form::text('task_name', old('task_name'), [
                                'class' => 'form-control',
                                'required' => 'required'
                            ]) }}
                                @error('task_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                {{ Form::label('task_type', 'Task Type') }}
                                {{ Form::select('task_type', [
                                LINKEDIN => 'LinkedIn',
                                XING => 'Xing',
                            ], old('task_type'), [
                                'class' => 'form-control',
                                'required' => 'required'
                            ]) }}
                                @error('task_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                {{ Form::label('login_email', 'Login Email') }}
                                {{ Form::email('login_email', old('login_email'), [
                                'class' => 'form-control',
                                'required' => 'required'
                            ]) }}
                                @error('login_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                {{ Form::label('login_password', 'Login Password') }}
                                {{ Form::password('login_password', [
                                'class' => 'form-control',
                                'required' => 'required'
                            ]) }}
                                @error('login_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                {{ Form::label('search_query', 'Search Query') }}
                                {{ Form::email('search_query', old('search_query'), [
                                'class' => 'form-control',
                                'required' => 'required'
                            ]) }}
                                @error('search_query')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                {{ Form::label('message_male', 'Message if Male') }}
                                {{ Form::textarea('message_male', old('message_male'), [
                                'class' => 'form-control',
                                'required' => 'required',
                                'maxLength' => '200',
                                'rows' => '5'
                            ]) }}
                                @error('message_male')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                {{ Form::label('message_female', 'Message if Female') }}
                                {{ Form::textarea('message_female', old('message_female'), [
                                'class' => 'form-control',
                                'required' => 'required',
                                'maxLength' => '200',
                                'rows' => '5'
                            ]) }}
                                @error('message_female')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                {{ Form::label('message_undetect', 'Message if Undetect') }}
                                {{ Form::textarea('message_undetect', old('message_undetect'), [
                                'class' => 'form-control',
                                'required' => 'required',
                                'maxLength' => '200',
                                'rows' => '5'
                            ]) }}
                                @error('message_undetect')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                {{ Form::label('request_delay', 'Request Delay') }}
                                {{ Form::number('request_delay', old('request_delay'), [
                                'class' => 'form-control',
                                'required' => 'required'
                            ]) }}
                                @error('request_delay')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                {{ Form::label('max_request', 'Max Request') }}
                                {{ Form::number('max_request', old('max_request'), [
                                'class' => 'form-control',
                                'required' => 'required'
                            ]) }}
                                @error('max_request')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                {{Form::submit("Run", [
                                'class' => 'btn btn-success float-right'
                            ])}}
                                <a class="btn btn-danger float-left" href="{{route('home')}}">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection