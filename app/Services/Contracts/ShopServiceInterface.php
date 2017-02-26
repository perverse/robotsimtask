<?php

namespace App\Services\Contracts;

interface ShopServiceInterface
{
    public function create(array $data);
    public function find($id);
    public function destroy($id);
    public function simulate($id);
}