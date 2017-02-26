<?php

namespace App\Repositories;

use App\Repositories\Contracts\ShopRepositoryInterface;

class ShopRepository extends Repository implements ShopRepositoryInterface
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