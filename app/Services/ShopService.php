<?php

namespace App\Services;

use App\Services\Contracts\ShopServiceInterface;
use App\Repositories\Contracts\ShopRepositoryInterface;
use Illuminate\Validation\Factory as Validator;
use App\Containers\ApiResponse;
use App\Services\SimulatorService;
use App\Exceptions\Simulator\RobotCollisionException;

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

    /**
     * Create new shop in database
     *
     * @return App\Containers\ApiResponse
     */
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
            return $this->response->make(['success' => false, 'errors' => $validator->messages()->toArray()]);
        }
    }

    /**
     * Find shop by ID
     *
     * @return App\Containers\ApiResponse
     */
    public function find($id)
    {
        $shop = $this->shop->find($id);

        if ($shop) {
            return $this->response->make(['success' => true, 'data' => $shop]);
        } else {
            return $this->response->make(['success' => false, 'error_type' => ApiResponse::ERROR_TYPE_NOT_FOUND]);
        }
    }

    /**
     * Delete shop by ID
     *
     * @return App\Containers\ApiResponse
     */
    public function destroy($id)
    {
        $this->shop->delete($id);

        return $this->response->make(['success' => true]);
    }

    /**
     * Simulate shop, save new state of shop, return new state of shop
     *
     * @return App\Containers\ApiResponse
     */
    public function simulate($id)
    {
        $shop = $this->shop->find($id);
        $service = $this;

        if ($shop) {
            try {
                $new_shop = $this->sim->simulate($shop->toArray());
            } catch (RobotCollisionException $e) {
                return $this->response->make(['success' => 'false', 'errors' => [$e->getMessage()]]);
            }

            $new_robots = collect($new_shop['robots']);

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