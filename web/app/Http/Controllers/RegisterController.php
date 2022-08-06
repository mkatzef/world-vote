<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;

use App\Models\User;
use App\Models\Tag;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Cookie;

class RegisterController extends Controller
{
  public function store()
  {
    if (!App::environment('local') && !$this->captchaIsValid()) {
      return redirect('/unsuccessful');
    }

    $attributes = request()->validate([
      'grid_row' => 'required',
      'grid_col' => 'required',
    ]);

    // TODO: ensure unique
    $attributes['access_token'] = $this->uniqid();

    $tags = [];
    foreach (Tag::all() as $t) {
       if (request()->has($t->slug)) {
         array_push($tags, $t->slug);
       }
    }
    $attributes['tags'] = json_encode($tags);

    $attributes['responses'] = '{}';

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

    $gr = request()->get('grid_row');
    $gc = request()->get('grid_col');
    if ($gr && $gc) {
      $updates['grid_row'] = $gr;
      $updates['grid_col'] = $gc;
    }

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
    if (!App::environment('local') && !$this->captchaIsValid()) {
      return redirect('/unsuccessful');
    }

    $user = User::where('access_token', '=', request()->get('access_token'))->first();
    auth()->login($user);
    return redirect("/");
  }

  private function captchaIsValid() {
    $captchaResponse = request()['g-recaptcha-response'];

    $secretKey = env('CAPTCHA_SECRET_KEY');
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captchaResponse);
    $response = file_get_contents($url);
    $responseKeys = json_decode($response, true);

    return $responseKeys['success'];
  }

  private function uniqid() {
    $id_int = random_int(0, 2e9);
    $id_candidate = base_convert($id_int, 10, 36);

    // 1 in 2 billion chance of repeating
    while (User::where('access_token', '=', $id_candidate)->exists()) {
      $id_int = random_int(0, 1e9);
      $id_candidate = base_convert($id_int, 10, 36);
    }

    return strval($id_candidate);
  }
}
