<?php

declare(strict_types=1);

/*
 * This file is part of the yassg project.
 *
 * (c) sigwin.hr
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sigwin\YASSG\Bridge\Symfony\Routing\Loader;

use Sigwin\YASSG\Bridge\Symfony\Controller\DefaultController;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RouteLoader implements RouteLoaderInterface
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
            $route = new Route(
                $route['path'].'/{_filename}',
                array_replace(
                    $route['defaults'] ?? [],
                    [
                        '_controller' => $route['defaults']['_controller'] ?? DefaultController::class,
                        '_filename' => null,
                    ]
                ),
                [
                    '_filename' => 'index\.html',
                ]
            );
            $collection->add($name, $route);
        }

        return $collection;
    }
}
