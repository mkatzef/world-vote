<?php

use Illuminate\Support\Facades\Route;
use App\Models\Tag;
use App\Models\Prompt;
use App\Http\Controllers\RegisterController;

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

Route::get('/', function () {
    return view('index', ['tags' => Tag::all(), 'prompts' => Prompt::all()]);
});

Route::post('new_vote', [RegisterController::class, 'store'])->middleware('guest');
Route::post('update_responses', [RegisterController::class, 'update_responses'])->middleware('auth');
Route::post('update_vote', [RegisterController::class, 'update']);
