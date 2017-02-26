<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\System\ApiResponseFormatter;
use App\Containers\ApiResponse as ServiceResponse;
// @todo: make this a non-concrete type

class ApiResponseJson
{
    protected $formatter;

    public function __construct(ApiResponseFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (isset($response->original) && $response->original instanceof ServiceResponse) {
            $response = $this->formatter->make($response->original)->toJsonResponse();
        }

        return $response;
    }
}