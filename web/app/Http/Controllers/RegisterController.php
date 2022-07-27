<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tag;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Cookie;

class RegisterController extends Controller
{
  public function store()
  {
    $attributes = request()->validate([
      'grid_row' => 'required',
      'grid_col' => 'required',
    ]);

    // TODO: ensure unique
    $attributes['access_token'] = uniqid();
    $attributes['share_token'] = 's_' . uniqid();

    $tags = [];
    foreach (Tag::all() as $t) {
       if (request()->has($t->slug)) {
         array_push($tags, $t->slug);
       }
    }
    $attributes['tags'] = json_encode($tags);

    $attributes['responses'] = '[]';

    auth()->login(User::create($attributes));

    if (request()->has('remember_me')) {
      $cookie = cookie('access_token', $attributes['access_token'], time() + (5 * 365 * 24 * 60 * 60));  // Expires in 5 years
      return redirect('/')->withCookie($cookie);
    } else {
      return redirect('/');
    }
  }

  public function update_responses()
  {
    $responses = json_decode(request()->user()->responses, true);
    foreach (request()->all() as $key => $value) {
      $p = Prompt::where('id', '=', $key)->first();
      if ($p) {
        $responses[$key] = intval($value);
      }
    }
    request()->user()->update(['responses' => json_encode($responses)]);
    if (request()->has('remember_me')) {
      $cookie = cookie('access_token', request()->user()['access_token'], time() + (5 * 365 * 24 * 60 * 60));  // Expires in 5 years
      return response("Success")->withCookie($cookie);
    } else {
      return response("Success")->withoutCookie('access_token');
    }
  }

  public function update_details()
  {
    $updates = [];

    // TODO
    /*if (request()->has('grid_row') && request()->has('grid_col')) {
      $updates['grid_row'] = request()->get('grid_row');
      $updates['grid_col'] = request()->get('grid_col');
    }*/

    $tags = [];
    foreach (Tag::all() as $t) {
       if (request()->has($t->slug)) {
         array_push($tags, $t->slug);
       }
    }
    $updates['tags'] = json_encode($tags);
    auth()->user()->update($updates);

    if (request()->has('remember_me')) {
      $cookie = cookie('access_token', request()->user()['access_token'], time() + (5 * 365 * 24 * 60 * 60));  // Expires in 5 years
      return redirect("/")->withCookie($cookie);
    } else {
      return redirect("/")->withoutCookie('access_token');
    }
  }

  public function login()
  {
    $user = User::where('access_token', '=', request()->get('access_token'))->first();
    auth()->login($user);
    return redirect("/");
  }
}
