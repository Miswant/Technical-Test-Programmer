<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['app' => config('app.name'), 'status' => 'running'];
});
