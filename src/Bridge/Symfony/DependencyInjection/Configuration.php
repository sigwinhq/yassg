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
                ->arrayNode('data_sources')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->enumNode('type')
                                ->values(['config', 'filesystem'])
                                ->isRequired()
                            ->end()
                            ->variableNode('values')
                                // only with type: config
                            ->end()
                            ->arrayNode('options')
                                // only with type: filesystem
                                ->children()
                                    ->scalarNode('path')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->validate()
                            ->ifTrue(static function (array $node): bool {
                                return $node['type'] === 'config' xor isset($node['values']);
                            })
                            ->thenInvalid('"values" must be configured only with "type: config"')
                        ->end()
                        ->validate()
                            ->ifTrue(static function (array $node): bool {
                                return $node['type'] === 'filesystem' xor isset($node['options']);
                            })
                            ->thenInvalid('"options" must be configured with "type: filesystem"')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
