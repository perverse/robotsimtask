<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\ShopServiceInterface;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    protected $shop;
    protected $request;

    public function __construct(ShopServiceInterface $shop, Request $request)
    {
        $this->shop = $shop;
        $this->request = $request;
    }

    public function create()
    {
        return $this->shop->create($this->request->all());
    }

    public function find($shop_id)
    {
        return $this->shop->find($shop_id);
    }

    public function destroy($shop_id)
    {
        return $this->shop->destroy($shop_id);
    }

    public function simulate($shop_id)
    {
        return $this->shop->simulate($shop_id);
    }
}