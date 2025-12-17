<?php

use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class);



Route::get('/users', \App\Livewire\User\UserIndex::class)->name('user.index');
Route::get('/users/create', \App\Livewire\User\UserCreate::class)->name('user.create');

Route::get('/users/{id}/edit', \App\Livewire\User\UserEdit::class)->name('user.edit');
Route::get('/users/{id}/show', \App\Livewire\User\UserShow::class)->name('user.show');
