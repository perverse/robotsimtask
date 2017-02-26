<?php

namespace App\Models;

class Robot extends Model
{
    public $fillable = ['x', 'y', 'heading', 'commands'];
}