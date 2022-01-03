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

final class RemoveCommandsCompilerPass implements CompilerPassInterface
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
