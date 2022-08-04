<?php

use Illuminate\Support\Facades\Route;
use App\Models\Prompt;
use App\Models\Tag;
use App\Models\Tileset;
use App\Models\User;
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
    $value = request()->cookie('access_token');
    if ($value) {
      auth()->login(User::where('access_token', '=', $value)->first());
    }
    return view('index', [
      'tags' => Tag::all(),
      'prompts' => Prompt::all(),
      'tileset' => Tileset::where('is_active', '=', '1')->first()
    ]);
});

Route::get('unsuccessful', function () {
  return 'Something went wrong! Please try again later';
});


Route::post('new_vote', [RegisterController::class, 'store'])->middleware('guest');
Route::post('update_responses', [RegisterController::class, 'update_responses'])->middleware('auth');
Route::post('update_details', [RegisterController::class, 'update_details'])->middleware('auth');
Route::post('login', [RegisterController::class, 'login']);
