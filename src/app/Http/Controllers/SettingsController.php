<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;
class SettingsController extends Controller
{

    public function __construct()
    {

    }

    public function index()
    {        
        $settings = Settings::all();
        return view('settings_create_form', ['settings' => $settings]);
    }

    public function save(Request $request)
    {
        $settings = Settings::all();
        $validate = [];
        foreach($settings as $setting){
            $validate[$setting->var_name] = 'required';
        }
        $request->validate($validate);   
        
        //Update Settings
        foreach($settings as $setting){
            if($setting->value != $request->input($setting->var_name)){
                $setting->value = $request->input($setting->var_name);
                $setting->save();
            }
        }
        return redirect('/home');
    }
}
