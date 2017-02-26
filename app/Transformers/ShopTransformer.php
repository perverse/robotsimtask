<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Shop;

class ShopTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['robots'];

    public function transform(Shop $item)
    {
        return [
            'id' => $item->getKey(),
            'width' => $item->width,
            'height' => $item->height
        ];
    }

    public function includeRobots(Shop $item)
    {
        $robots = $item->robots;

        return $this->collection($robots, new RobotTransformer);
    }
}