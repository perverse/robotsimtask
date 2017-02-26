<?php

namespace App\Repositories;

class ShopRepository extends Repository
{
    public function useModel()
    {
        return '\App\Models\Shop';
    }
}