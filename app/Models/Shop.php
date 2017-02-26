<?php

namespace App\Models;

class Shop extends Model
{
    protected $collection = 'shops';
    public $fillable = ['width', 'height'];

    public function robots()
    {
        return $this->embedsMany("App\Models\Robot");
    }
}