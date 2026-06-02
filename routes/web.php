<?php

use App\Http\Controllers\KnowledgeBotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [KnowledgeBotController::class, 'index']);
Route::post('/ask', [KnowledgeBotController::class, 'ask'])->name('knowledge.ask');
