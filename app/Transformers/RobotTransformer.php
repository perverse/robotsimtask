<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Robot;

class RobotTransformer extends TransformerAbstract
{
    public function transform(Robot $item)
    {
        return [
            'id' => $item->getKey(),
            'x' => $item->x,
            'y' => $item->y,
            'heading' => $item->heading
        ];
    }
}