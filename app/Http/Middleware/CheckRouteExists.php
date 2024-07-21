<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class CheckRouteExists
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            $this->router->getRoutes()->getByName($request->route()->getName());
        } catch (RouteNotFoundException $e) {
            return response()->json(['error' => 'Route not found'], 404);
        }

        return $next($request);
    }
}
