<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('add-peer', [\App\Http\Controllers\VideoController::class, 'addPeer']);

Route::post('call', [\App\Http\Controllers\VideoController::class, 'call']);
Route::post('answer', [\App\Http\Controllers\VideoController::class, 'answer']);

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [\App\Http\Controllers\VideoController::class, 'index']);

Broadcast::routes();