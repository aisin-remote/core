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

Route::get('/', function () {
    return view('website.dashboard.index');
});

Route::get('/login', function(){
    return view('website.auth.login');
});
Route::get('/register', function(){
    return view('website.auth.register');
});

Route::prefix('hav')->group(function () {
    Route::get('/', function (){
        return view('website.hav.index');
    });
});
Route::prefix('employee')->group(function () {
    Route::get('/', function (){
        return view('website.employee.index');
    });
});
Route::prefix('rtc')->group(function () {
    Route::get('/', function (){
        return view('website.rtc.index');
    });
});