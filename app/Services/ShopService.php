<?php

namespace App\Services;

use App\Services\Contracts\ShopServiceInterface;
use App\Repositories\Contracts\ShopRepositoryInterface;
use Illuminate\Validation\Factory as Validator;

class ShopService implements ShopServiceInterface
{
    protected $shop;
    protected $validator;

    public function __construct(ShopRepositoryInterface $shop, Validator $validator, ServiceResponse $response)
    {
        $this->shop = $shop;
        $this->validator = $validator;
        $this->response = $response;
    }

    public function create(array $data)
    {
        $validator = $this->validator->make($data, [
            'width' => 'required|numeric',
            'height' => 'required|numeric'
        ]);

        if ($validator->passes()) {
            $shop = $this->shop->create($data);
            return $this->response->make(['success' => true, 'data' => $shop]);
        } else {
            return $this->response->make(['success' => false, 'errors' => $validator->messages()]);
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