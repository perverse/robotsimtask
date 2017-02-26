<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Robot extends Eloquent
{
    public $fillable = ['x', 'y', 'heading'];
}