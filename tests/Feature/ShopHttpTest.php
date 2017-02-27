<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Shop;
use App\Models\Robot;

class ShopHttpTest extends TestCase
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
    public function testPostShop()
    {
        $response = $this->json('POST', '/api/shop', [
            'width' => 5,
            'height' => 5
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'result' => [
                         'id' => true,
                         'width' => true,
                         'height' => true,
                     ],
                     'status' => true
                 ]);
    }

    public function testShopGet()
    {
        $shop = $this->makeTestShop();

        $response = $this->json('GET', '/api/shop/' . $shop->_id);

        $response->assertStatus(200)
                 ->assertExactJson([
                     'result' => [
                         'id' => $shop->_id,
                         'width' => $shop->width,
                         'height' => $shop->height
                     ],
                     'status' => 'ok'
                 ]);
    }

    public function testShopDelete()
    {
        $shop = $this->makeTestShop();

        $response = $this->json('DELETE', '/api/shop/' . $shop->_id);

        $response->assertStatus(200)
                 ->assertExactJson([
                    'status' => 'ok'
                 ]);
    }

    public function testShopExecute()
    {
        $shop = $this->makeTestShop();
        $shop = $this->makeTestRobots($shop);

        $response = $this->json('POST', '/api/shop/' . $shop->_id . '/execute');

        var_dump($shop->robots);

        $response->assertStatus(200)
                 ->assertExactJson([
                     'status' => 'ok',
                     'result' => [
                         'id' => $shop->_id,
                         'width' => $shop->width,
                         'height' => $shop->height,
                         'robots' => [
                             ['x' => 3, 'y' => 0, 'heading' => 'E', 'commands' => 'LMMM', 'id' => $shop->robots[0]->_id],
                             ['x' => 1, 'y' => 4, 'heading' => 'W', 'commands' => 'LMMM', 'id' => $shop->robots[1]->_id]
                         ]
                     ]
                 ]);
    }

}
