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
    return view('welcome');
});

Route::get('posts', 'PostController@index');
Route::get('createnew', 'PostController@createnew');

// Route::post('/posts','PostController@store');

Route::get('feed', 'Feed@feedSender');

Route::get('user/{id}', 'Feed@UserInfo');

Route::post('update_user', 'Feed@UpdateUser');

Route::get('userPersonalInfo/{id}', 'Feed@userPersonalInfo');

Route::post('update_userPersonalInfo', 'Feed@updateUserPersonalInfo');