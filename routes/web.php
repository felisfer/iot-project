<?php

use App\Http\Controllers\ESP32Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', [ESP32Controller::class, 'dashboard']);
