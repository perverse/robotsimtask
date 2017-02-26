<?php

namespace App\Services;

use App\Services\Contracts\ShopServiceInterface;
use App\Repositories\Contracts\ShopRepositoryInterface;
use Illuminate\Validation\Factory as Validator;

class ShopService implements ShopServiceInterface
{
    protected $shop;
    protected $validator;

    public function __construct(ShopRepositoryInterface $shop, Validator $validator)
    {
        $this->shop = $shop;
        $this->validator = $validator;
    }

    public function create(array $data)
    {
        $validator = $this->validator->make($data, [
            'width' => 'required|numeric',
            'height' => 'required|numeric'
        ]);

        if ($validator->passes()) {
            
        }
    }

    public function find($id)
    {

    }

    public function destroy($id)
    {

    }

    public function simulate($id)
    {

    }
}