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
                ->arrayNode('variables')
                    ->scalarPrototype()
                    ->end()
                ->end()
                ->arrayNode('routes')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('path')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->booleanNode('skip')
                                ->setDeprecated('sigwin/yassg', '0.5.0', 'Node "skip" at %path% is deprecated, use "options.skip"')
                                ->defaultFalse()
                            ->end()
                            ->variableNode('defaults')
                            ->end()
                            ->variableNode('catalog')
                                ->setDeprecated('sigwin/yassg', '0.5.0', 'Node "catalog" at %path% is deprecated, use "options.catalog"')
                            ->end()
                            ->arrayNode('options')
                                ->children()
                                    ->arrayNode('headers')
                                        ->scalarPrototype()
                                        ->end()
                                    ->end()
                                    ->arrayNode('strip_parameters')
                                        ->scalarPrototype()
                                        ->end()
                                    ->end()
                                    ->booleanNode('skip')
                                        ->defaultFalse()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->variableNode('database')
                    ->setDeprecated('sigwin/yassg', '0.5.0')
                ->end()
                ->arrayNode('databases')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('storage')
                                ->isRequired()
                            ->end()
                            ->scalarNode('class')
                                ->isRequired()
                            ->end()
                            ->scalarNode('page_limit')
                                ->defaultValue(20)
                                ->cannotBeEmpty()
                                ->info('How many items per page are by default used when paginating this database')
                            ->end()
                            ->variableNode('options')
                                // only with type: filesystem
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
