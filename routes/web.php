<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeGreetingController;
Route::get('/', function () {
    return abort(404);
});

Route::post('send','ApiController@send');
Route::get('polis-verify/{id}',[WelcomeGreetingController::class, 'formVerify']);
Route::post('polis-verify/{id}',[WelcomeGreetingController::class, 'verify']);
