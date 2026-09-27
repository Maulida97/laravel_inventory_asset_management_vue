<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
});

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');