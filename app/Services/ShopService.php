<?php

namespace App\Services;

use App\Services\Contracts\ShopServiceInterface;
use App\Repositories\Contracts\ShopRepositoryInterface;
use Illuminate\Validation\Factory as Validator;
use App\Containers\ApiResponse;
use App\Services\SimulatorService;

class ShopService implements ShopServiceInterface
{
    protected $shop;
    protected $validator;
    protected $response;
    protected $sim;

    public function __construct(
        ShopRepositoryInterface $shop,
        Validator $validator,
        ApiResponse $response,
        SimulatorService $sim
    ) {
        $this->shop = $shop;
        $this->validator = $validator;
        $this->response = $response;
        $this->sim = $sim;
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
        $shop = $this->shop->find($id);

        if ($shop) {
            return $this->response->make(['success' => true, 'data' => $shop]);
        } else {
            return $this->response->make(['success' => false, 'error_type' => ApiResponse::ERROR_TYPE_NOT_FOUND]);
        }
    }

    public function destroy($id)
    {
        $this->shop->delete($id);

        return $this->response->make(['success' => true]);
    }

    public function simulate($id)
    {
        $shop = $this->shop->find($find);
        $service = $this;

        if ($shop) {
            $shop_arr = $shop->toArray();
            $new_shop = $this->sim->simulate($shop);
            $new_robots = collect($new_robots);

            $shop->robots->each(function($robot) use (&$new_robots, &$shop, &$service){
                $new_data = $new_robots->first(function($data) use ($robot){
                    return $data['_id'] == $robot->_id;
                });

                if ($new_data) {
                    $robot->x = $new_data['x'];
                    $robot->y = $new_data['y'];
                    $robot->heading = $new_data['heading'];

                    $service->shop->updateRobot($shop, $robot);
                }
            });

            return $this->response->make(['success' => true, 'data' => $shop]);
        } else {
            return $this->response->make(['success' => false, 'error_type' => ApiResponse::ERROR_TYPE_NOT_FOUND]);
        }
    }
}