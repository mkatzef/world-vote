<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tag;
use App\Models\Prompt;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
  public function store()
  {
    $attributes = request()->validate([
      'grid_row' => 'required',
      'grid_col' => 'required',
    ]);

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

    return redirect('/');
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
    return "";
  }
}
