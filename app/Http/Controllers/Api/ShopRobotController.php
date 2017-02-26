<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\RobotServiceInterface;

class ShopRobotController extends Controller
{
    public function __construct(RobotServiceInterface $robot, Request $request)
    {
        $this->robot = $robot;
        $this->request = $request;
    }

    public function create()
    {
        return $this->robot->create($this->request->all());
    }

    public function update($shop_id, $robot_id)
    {
        return $this->robot->update($shop_id, $robot_id, $this->request->all());
    }

    public function destroy($shop_id, $robot_id)
    {
        return $this->robot->destroy($shop_id, $robot_id);
    }
}