<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = array(
      'access_token',
      'grid_row',
      'grid_col',
      'tags',
      'responses',
      'created_prompts'
    );
}
