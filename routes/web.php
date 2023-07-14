<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::post('/home', [HomeController::class, 'index'])->name('home');
Route::get('message/{id}',[HomeController::class,'getMessage'])->name('message');
Route::get('message',[HomeController::class,'sendMessage'])->name('message')->name('send-message');
