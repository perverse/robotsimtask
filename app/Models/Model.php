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
        return array_keys($this->relations);
    }
}