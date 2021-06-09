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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/coin', 'HomeController@coin')->name('coin');

Route::get('/wallet/{code}','WalletController@show')->name('wallet.show');
Route::get('/wallet/{code}/activate','WalletController@activate')->name('wallet.activate');
