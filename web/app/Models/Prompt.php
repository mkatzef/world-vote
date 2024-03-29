<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = array(
      'caption',
      'is_mapped',
      'option0',
      'option1',
      'count_ratios',
      'creator_id',
      'reviewed',
      'auth_code',
    );

    protected $hidden = ['auth_code'];
}
