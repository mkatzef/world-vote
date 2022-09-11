<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;

use App\Models\User;
use App\Models\Tag;
use App\Models\TagType;
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
    foreach (TagType::all() as $tt) {
       if (request()->has($tt->slug) &&
        Tag::where('slug', '=', request()[$tt->slug])->exists()) {
         array_push($tags, request()[$tt->slug]);
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
    foreach (TagType::all() as $tt) {
       if (request()->has($tt->slug) &&
        Tag::where('slug', '=', request()[$tt->slug])->exists()) {
         array_push($tags, request()[$tt->slug]);
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

    $matching_users = User::where('access_token', '=', request()->get('access_token'));
    if ($matching_users->count() == 0) {
      return redirect('/unsuccessful');
    }
    auth()->login($matching_users->first());
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

  private function idPart() {
    return strval(base_convert(random_int(0, 1e7), 10, 36));
  }
  private function anId() {
    return $this->idPart() . $this->idPart();
  }

  private function uniqid() {
    $id_candidate = $this->anId();

    while (User::where('access_token', '=', $id_candidate)->exists()) {
      $id_candidate = $this->anId();
    }

    return $id_candidate;
  }
}
