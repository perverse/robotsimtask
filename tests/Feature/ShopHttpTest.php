<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ShopHttpTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test creation of shop through CLI interface
     *
     * @return void
     */
    public function testPostshop()
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


}
