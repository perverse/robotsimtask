<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\RobotServiceInterface;
use Illuminate\Http\Request;

class ShopRobotController extends Controller
{
    public function __construct(RobotServiceInterface $robot, Request $request)
    {
        $this->robot = $robot;
        $this->request = $request;
    }

    public function create($shop_id)
    {
        return $this->robot->create($shop_id, $this->request->all());
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