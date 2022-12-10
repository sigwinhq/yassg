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

namespace Sigwin\YASSG\Bridge\Symfony\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Route;

final class InjectRouteVariablesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $routes = $container->getParameter('sigwin_yassg.routes');
        if (! is_iterable($routes)) {
            throw new \LogicException('Invalid sigwin_yassg.routes parameter');
        }
        /** @var array<string, string> $routesVariables */
        $routesVariables = $container->getParameter('sigwin_yassg.routes.variables');

        foreach ($routes as $name => $spec) {
            $route = new Route($spec['path']);
            $compiled = $route->compile();
            $variables = $compiled->getVariables();

            foreach ($variables as $variable) {
                if (isset($routesVariables[$variable])) {
                    $routes[$name]['variables'][] = $variable;
                    if (! isset($routes[$name]['catalog'][$variable])) {
                        $routes[$name]['catalog'][$variable] = $routesVariables[$variable];
                    }
                }
            }
        }
        $container->setParameter('sigwin_yassg.routes', $routes);
    }
}
