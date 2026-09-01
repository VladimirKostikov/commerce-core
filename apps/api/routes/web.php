<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('Application is running.', 200);
});
