<?php

namespace App\Repositories;

class ShopRepository extends Repository
{
    public function useModel()
    {
        return '\App\Models\Shop';
    }

    public function saveRobot($shop, $robot)
    {
        return $shop->robots()->save($robot);
    }
}