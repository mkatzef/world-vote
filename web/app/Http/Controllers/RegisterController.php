<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tag;
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
}
