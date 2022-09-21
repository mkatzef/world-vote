<?php

use Illuminate\Support\Facades\Route;
use App\Models\Prompt;
use App\Models\Tag;
use App\Models\TagType;
use App\Models\General;
use App\Models\User;
use Carbon\Carbon;
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

function main($prompts, $is_query=false) {
  $value = request()->cookie('access_token');
  if ($value) {
    auth()->login(User::where('access_token', '=', $value)->first());
  }
  $user_count_increment = 10;
  $law_data = General::where('property', '=', 'law_tileset_id')->first();
  return view('index', [
    'tags' => Tag::all(),
    'tag_types' => TagType::all(),
    'prompts' => $prompts,
    'tileset_id' => General::where('property', '=', 'active_tileset_id')->first()->value('pvalue'),
    'law_tileset_id' => $law_data['pvalue'],
    'law_prompt_ids' => $law_data['extra'],
    'last_updated' =>
      Carbon::createFromFormat(
        'Y-m-d H:i:s',
        General::where('property', '=', 'active_tileset_id')->first()->value('last_written')
      ),
    'n_voters' => $user_count_increment * intdiv(User::count(), $user_count_increment),
    'is_query' => $is_query,
  ]);
}

function get_paginator($key='id', $order='asc') {
  return Prompt::where('reviewed', 1)->orderBy($key, $order)->cursorPaginate(10)->withPath('/pages/' . $key . '/' . $order);
}

Route::get('/', function () {
  return main(get_paginator());
});

Route::get('/pages/{key}/{order}', function ($k, $o) {
  return get_paginator($k, $o);
});

Route::get('/poll/{pId}', function ($pId) {
  return main(Prompt::where('id', $pId)->get(), $is_query=true);
});

Route::get('unsuccessful', function () {
  return 'Something went wrong! Please try again later<br><a href="/"><button>Home</button></a>';
});

Route::get('login_failed', function () {
  return 'No votes were found with that access token!<br><a href="/"><button>Home</button></a>';
});

Route::get('logout', function () {
  Session::flush();
  Auth::logout();
  return redirect('/')->withoutCookie('access_token');
})->middleware('auth');

Route::post('new_vote', [RegisterController::class, 'store'])->middleware('guest');
Route::post('update_responses', [RegisterController::class, 'update_responses'])->middleware('auth');
Route::post('update_details', [RegisterController::class, 'update_details'])->middleware('auth');
Route::post('create_poll', [RegisterController::class, 'create_prompt'])->middleware('auth');
Route::post('login', [RegisterController::class, 'login']);
