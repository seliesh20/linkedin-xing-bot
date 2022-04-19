@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="col-md-2 float-left">{{ __("Settings") }}</div>                
                    <div class="text-right col-md-2 float-right"></div>
                </div>
                <div class="card-body">
                    <form class="needs-validation {{$errors->any()?'was-validated':''}}" name="settings_create_form" id="settings-create-form" method="post" action="{{route('settings.save')}}" novalidate>
                    <div class="col-md-2 float-left">&nbsp;</div> 
                    <div class="col-md-5 float-left">
                    {{Form::token()}}
                    @foreach($settings as $setting)
                        <div class="form-group">                                                                    
                                {{ Form::label($setting->var_name, $setting->var_full_name) }}
                                {{ Form::text($setting->var_name, $setting->value, [
                                    'class' => 'form-control',
                                    'required' => 'required'
                                ]) }}
                                @error($setting->var_name)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            
                        </div>
                    @endforeach
                    <div class="form-group">
                            {{Form::submit("Save", [
                                'class' => 'btn btn-success float-right'
                            ])}}
                            <a class="btn btn-danger float-left" href="{{route('home')}}">Cancel</a>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection