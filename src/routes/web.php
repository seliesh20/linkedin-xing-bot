<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register'=>true]);

Route::get('/', function () {    
    return redirect('/login');
});
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/tasks/view/{task_id}', [App\Http\Controllers\TaskController::class, 'view'])->name('task.view')->where('task_id', '[0-9]+');
Route::post('/tasks/list', [App\Http\Controllers\TaskController::class, 'getTasks'])->name('task.list');
Route::get('/tasks/create', [App\Http\Controllers\TaskController::class, 'create'])->name('task.create');
Route::post('/tasks/save', [App\Http\Controllers\TaskController::class, 'save'])->name('task.save');
Route::get('/index',[App\Http\Controllers\IndexController::class, 'index']);

Route::get('/run', [App\Http\Controllers\BotController::class, 'runTask'])
    ->name('task.run');
Route::get('/withdraw', [App\Http\Controllers\BotController::class, 'withdrawTask'])
    ->name('task.withdraw');

Route::get('/testrun', [App\Http\Controllers\BotController::class, 'testTask'])
    ->name('task.test');    

//Settings
Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name("settings.index");
Route::post('/settings/save', [App\Http\Controllers\SettingsController::class, 'save'])->name("settings.save");




