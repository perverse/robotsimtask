<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Model extends Eloquent
{
    public function relationshipIsLoaded($relationship)
    {
        return array_key_exists($relationship, $this->relations);
    }

    public function getLoadedRelationships()
    {
        $relations = array_keys($this->relations);

        // pick up mongo embedded relations
        foreach ($this->attributes as $index => $attribute) {
            if (is_array($attribute)) {
                $relations[] = $index;
            }
        }

        return $relations;
    }
}