<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class FallbackTransformer extends TransformerAbstract
{
    public function transform($item)
    {
        if (is_array($item)) {
            return $item;
        } else {
            return [
                'id' => (int) $item->id,
                'class_name' => get_class($item)
            ];
        }
    }
}