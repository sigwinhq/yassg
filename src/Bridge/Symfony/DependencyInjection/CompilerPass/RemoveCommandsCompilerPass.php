<?php

namespace Sigwin\YASSG\Bridge\Symfony\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveCommandsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $commands = $container->findTaggedServiceIds('console.command');
        foreach (array_keys($commands) as $id) {
            $definition = $container->getDefinition($id);

            $className = $definition->getClass();
            if (null === $className) {
                continue;
            }

            if (0 !== mb_strpos($className, 'Sigwin')) {
                $container->removeDefinition($id);
            }
        }        
    }
}
