<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Shop;
use App\Models\Robot;

class RobotHttpTest extends TestCase
{
    public function tearDown()
    {
        Shop::truncate();
        Robot::truncate();
    }

    public function makeTestShop()
    {
        // this should be mocked and factoried... but having a fight with mongodb laravel driver.
        $shop = new Shop([
            'width' => 10,
            'height' => 10,
        ]);

        $shop->save();

        return $shop;
    }

    public function makeTestRobots($shop)
    {
        $robots = collect([
            ['x' => 0, 'y' => 0, 'heading' => 'S', 'commands' => 'LMMM'],
            ['x' => 4, 'y' => 4, 'heading' => 'N', 'commands' => 'LMMM']
        ]);

        $robots->each(function($robot, $key) use ($shop) {
            $robot_model = $shop->robots()->create($robot);
            $robot_model->save();
        });

        $shop->save();

        return $shop;
    }

    /**
     * Test creation of shop through CLI interface
     *
     * @return void
     */
    public function testPostRobot()
    {
        $shop = $this->makeTestShop();

        $response = $this->json('POST', '/api/shop/' . $shop->_id . '/robot', [
            'x' => 2,
            'y' => 2,
            'heading' => 'N',
            'commands' => 'LMMM'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'result' => [
                         'x' => true,
                         'y' => true,
                         'heading' => true,
                         'commands' => true
                     ],
                     'status' => true
                 ]);
    }

    public function testPutRobot()
    {
        $shop = $this->makeTestShop();
        $robot = $shop->robots()->create(['x' => 4, 'y' => 4, 'heading' => 'N', 'commands' => 'LMMM']);
        $shop->save();
        $robot->save();

        $response = $this->json('PUT', '/api/shop/' . $shop->_id . '/robot/' . $robot->_id, [
            'x' => 3,
            'y' => 3,
            'heading' => 'S',
            'commands' => 'RMMM'
        ]);

        $response->assertStatus(200)
                 ->assertExactJson([
                     'result' => [
                         'id' => $robot->_id,
                         'x' => 3,
                         'y' => 3,
                         'heading' => 'S',
                         'commands' => 'RMMM'
                     ],
                     'status' => 'ok'
                 ]);
    }

    public function testDeleteRobot()
    {
        $shop = $this->makeTestShop();
        $robot = $shop->robots()->create(['x' => 4, 'y' => 4, 'heading' => 'N', 'commands' => 'LMMM']);
        $shop->save();
        $robot->save();

        $response = $this->json('DELETE', '/api/shop/' . $shop->_id . '/robot/' . $robot->_id);

        $response->assertStatus(200)
                 ->assertExactJson([
                     'status' => 'ok'
                 ]);
    }

}
