<?php

use App\Models\Customer;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('customers', function () {
        return Inertia::render('customers', ['customers' => Customer::all()]);
    })->name('customers');

    Route::get('customers/{id}', function (string $id) {
        return Inertia::render('customer', ['customer' => Customer::all()->where('id', $id)->first()]);
    })->name('customers');
    
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
