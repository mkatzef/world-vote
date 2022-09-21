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

  public function create_prompt()
  {
    $fail_ret = response("Something went wrong!");

    if (!(request()->has('summary') && request()->has('prompt') && request()->has('answer_type'))) {
      return $fail_ret;
    }

    $created_prompts = request()->user()->created_prompts;
    if ($created_prompts == null) {
      $created_prompts = [];
    } else {
      $created_prompts = json_decode($created_prompts, true);
      $n_last_24h = 0;
      $time_cutoff = time() - (24 * 60 * 60);
      foreach ($created_prompts as $p_id => $t) {
        if ($t > $time_cutoff) {
          $n_last_24h++;
        }
      }
      if ($n_last_24h >= 5) {
        return $fail_ret;
      }
    }

    $summary = request()['summary'];
    $caption = request()['prompt'];

    $option0 = '';
    $option1 = '';
    switch (request()['answer_type']) {
      case 'yes_no':
        $option0 = 'Yes';
        $option1 = 'No';
        break;
      case 'zero_ten':
        $option0 = '0';
        $option1 = '10';
        break;

      case 'high_low':
        $option0 = 'High';
        $option1 = 'Low';
        break;
      case 'good_bad':
        $option0 = 'Good';
        $option1 = 'Bad';
        break;
    }
    if ($option0 == '' || $option1 == '') {
      return $fail_ret;
    }

    $attributes = [
      'summary' => $summary,
      'caption' => $caption,
      'option0' => $option0,
      'option1' => $option1,
      'count_ratios' => '{"all":[0,0,0,0,0,0,0,0,0,0,0]}',
      'creator_id' => request()->user()->id,
    ];

    $p = Prompt::create($attributes);
    $created_prompts[$p->id] = time();
    request()->user()->update(['created_prompts' => json_encode($created_prompts)]);

    return redirect('/poll/' . $p->id);
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
