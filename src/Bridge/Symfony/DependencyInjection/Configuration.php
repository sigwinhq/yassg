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

namespace Sigwin\YASSG\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sigwin_yassg');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('routes')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('path')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->variableNode('defaults')
                            ->end()
                            ->variableNode('catalog')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->variableNode('database')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
