<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = array(
      'summary',
      'caption',
      'option0',
      'option1',
      'count_ratios',
    );
}
