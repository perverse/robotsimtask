<?php

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
    $parsedown = new \Parsedown();

    return view('welcome', ['markdown' => $parsedown->text(file_get_contents(base_path('readme.md')))]);
});
