<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AutoBot\Bot;
use Laravel\Dusk\Browser;
use App\Models\TaskCookies;

class IndexController extends Controller
{
    //
    public function index()
    {
        $cookie = TaskCookies::where("login_email", "testlinkedinxing@hotmail.com")
            ->where("login_password", "Test@2585")->first();
            var_dump($cookie);

        /*Bot::browse(function($browser){            
            $browser->visit("https://www.google.ae")
                ->type('input.gLFyf', 'test')
                ->screenshot('google');
        });*/        
    }
}
