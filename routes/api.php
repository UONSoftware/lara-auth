<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', 'LoginController@login');
Route::post('/request-password-change', 'ChangePasswordController@requestNewPassword');
Route::middleware('jwt.auth')->delete('/revoke', 'LoginController@revokeRefreshToken');
Route::middleware('jwt.auth')
    ->patch('/change-password', 'ChangePasswordController@changePassword')
    ->name('change-password');


if(config('lara_auth.register', false) === true)
{
    Route::post('/register', 'RegisterController@register');
}
