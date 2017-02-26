<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Shop extends Eloquent
{
    protected $collection = 'shops';
    public $fillable = ['width', 'height'];

    public function robots()
    {
        return $this->embedsMany("App\Models\Robot");
    }
}