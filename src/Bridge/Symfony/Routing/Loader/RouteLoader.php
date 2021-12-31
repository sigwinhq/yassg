<?php

namespace Sigwin\YASSG\Bridge\Symfony\Routing\Loader;

use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader implements RouteLoaderInterface
{
    public function __invoke(): RouteCollection
    {
        $collection = new RouteCollection();
        
        $route = new Route('/', [
            '_controller' => 'Sigwin\YASSG\Bridge\Symfony\Controller\DefaultController',
        ]);
        $collection->add('index', $route);
        
        return $collection;
    }
}
