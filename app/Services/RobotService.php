<?php

namespace App\Services;

use App\Services\Contracts\RobotServiceInterface;
use App\Containers\ApiResponse;
use App\Repositories\Contracts\ShopRepositoryInterface;
use Illuminate\Validation\Factory as Validator;

class RobotService implements RobotServiceInterface
{
    protected $shop;
    protected $response;
    protected $validator;

    public function __construct(ShopRepositoryInterface $shop, ApiResponse $response, Validator $validator)
    {
        $this->shop = $shop;
        $this->response = $response;
        $this->validator = $validator;
    }

    public function getValidator($data)
    {
        return $this->validator->make($data, [
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'heading' => 'required|in:N,E,S,W'
        ]);
    }

    public function create($shop_id, $data)
    {
        $validator = $this->getValidator($data);

        if ($validator->fails()) {
            return $this->response->make(['success' => false, 'errors' => $validator->messages()->toArray()]);
        }

        $shop = $this->shop->find($shop_id);

        if ($shop) {
            $robot = $shop->robots()->create($data);

            return $this->response->make(['success' => true, 'data' => $robot]);
        } else {
            return $this->response->make(['success' => false, 'error_type' => ApiResponse::ERROR_TYPE_NOT_FOUND]);
        }
    }

    public function update($shop_id, $robot_id, $data)
    {
        $validator = $this->getValidator($data);

        if ($validator->fails()) {
            return $this->response->make(['success' => false, 'errors' => $validator->messages()->toArray()]);
        }

        $shop = $this->shop->find($shop_id);

        if ($shop) {
            $robot = $shop->robots->first(function($data) use ($robot_id){
                return $data['_id'] == $robot_id;
            });

            if (!$robot) {
                return $this->response->make(['success' => false, 'error_type' => ApiResponse::ERROR_TYPE_NOT_FOUND]);
            }

            // this manual assignment is pretty dorky. mongo forcing me this way. not a big deal, the scale gains from mongo outweigh the php overhead substantially
            $robot->x = $data['x'];
            $robot->y = $data['y'];
            $robot->heading = $data['heading'];
            $robot->commands = $data['commands'];

            $shop->robots()->save($robot); // should update existing model...

            return $this->response->make(['success' => true, 'data' => $robot]);
        } else {
            return $this->response->make(['success' => false, 'error_type' => ApiResponse::ERROR_TYPE_NOT_FOUND]);
        }
    }

    public function destroy($shop_id, $robot_id)
    {
        $shop = $this->shop->find($shop_id);

        if ($shop) {
            $shop->robots()->destroy($robot_id);

            return $this->response->make(['success' => true]);
        } else {
            return $this->response->make(['success' => false, 'error_type' => ApiResponse::ERROR_TYPE_NOT_FOUND]);
        }
    }
}