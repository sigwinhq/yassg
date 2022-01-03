<?php

namespace Sigwin\YASSG\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
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
