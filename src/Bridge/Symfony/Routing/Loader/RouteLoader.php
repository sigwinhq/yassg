<?php

namespace Sigwin\YASSG\Bridge\Symfony\Routing\Loader;

use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Sigwin\YASSG\Bridge\Symfony\Controller\DefaultController;

class RouteLoader implements RouteLoaderInterface
{
    private array $routes;
    
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }
    
    public function __invoke(): RouteCollection
    {
        $collection = new RouteCollection();
        
        foreach ($this->routes as $name => $route) {
            $route = new Route($route['path'], array_replace(
                $route['_defaults'] ?? [],
                [
                    '_controller' => DefaultController::class,
                ]
            ));
            $collection->add($name, $route);
        }
        
        return $collection;
    }
}
