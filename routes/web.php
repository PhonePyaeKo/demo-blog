<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::livewire('/posts', 'pages::posts.index')
        ->middleware('can:create posts')
        ->name('posts.index');
    Route::livewire('/posts/create', 'pages::posts.create')
        ->middleware('can:create posts')
        ->name('posts.create');
    Route::livewire('/posts/{post}/edit', 'pages::posts.edit')
        ->name('posts.edit');

    Route::livewire('/users', 'pages::users.index')
        ->middleware('can:create users')
        ->name('users.index');
});

require __DIR__.'/settings.php';
