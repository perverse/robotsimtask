<?php

namespace App\Fractal\Serializers;

use League\Fractal\Serializer\DataArraySerializer;

class CustomDataArraySerializer extends DataArraySerializer
{
    public function collection($resourceKey, array $data)
    {
        if (!empty($resourceKey)) {
            return [$resourceKey => $data];
        } else {
            return $data;
        }
    }

    public function item($resourceKey, array $data)
    {
        if (!empty($resourceKey)) {
            return [$resourceKey => $data];
        } else {
            return $data;
        }
    }
}