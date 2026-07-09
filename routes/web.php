<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

Route::get('/login', fn() => view('auth.login'))->name('login');

Route::get('/', fn() => redirect()->route('dashboard'));
Route::get('/dashboard', fn() => view('dashboard.index'))->name('dashboard');
Route::get('/items', fn() => view('items.index'))->name('items');
Route::get('/categories', fn() => view('categories.index'))->name('categories');
Route::get('/manifests', fn() => view('manifests.index'))->name('manifests');
Route::get('/manifests/outbound', fn() => view('manifests.outbound'))->name('manifests.outbound');
Route::get('/manifests/inbound', fn() => view('manifests.inbound'))->name('manifests.inbound');
Route::get('/stock', fn() => view('stock.index'))->name('stock');
Route::get('/stock/history', fn() => view('stock.history'))->name('stock.history');
Route::get('/incidents', fn() => view('incidents.index'))->name('incidents');
Route::get('/events', fn() => view('events.index'))->name('events');
Route::get('/flightcases', fn() => view('flightcases.index'))->name('flightcases');
Route::get('/users', fn() => view('users.index'))->name('users');
Route::get('/403', fn() => abort(403))->name('403');
