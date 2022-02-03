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

use Sigwin\YASSG\DataSource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface as OptionsResolverException;

final class ConfigureDataSourcesCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        $supportedTypes = [];
        $references = $this->findAndSortTaggedServices('sigwin_yassg.abstract.data_source_type', $container);
        foreach ($references as $reference) {
            $reference = $reference->__toString();
            $definition = $container->getDefinition($reference);

            /** @var class-string<DataSource> $class */
            $class = $definition->getClass();
            $callable = [$class, 'getType'];
            $type = $callable();

            if (isset($supportedTypes[$type])) {
                throw new \LogicException(sprintf('Data source type %1$s already provided by %2$s', $type, $reference));
            }

            $supportedTypes[$type] = $class;
            $container->removeDefinition($reference);
        }
        unset($type);

        /** @var array<string, array{type: string, options?: array}> $configuredDataSources */
        $configuredDataSources = $container->getParameter('sigwin_yassg.data_sources_spec');
        foreach ($configuredDataSources as $name => $configuredDataSource) {
            $type = $configuredDataSource['type'];

            if (\array_key_exists($type, $supportedTypes) === false) {
                throw new \LogicException(sprintf('Unsupported type "%1$s" at "sigwin_yassg.data_sources.%2$s", allowed values: %3$s', $type, $name, implode(', ', array_keys($supportedTypes))));
            }

            $definition = new Definition($supportedTypes[$type]);
            $definition
                ->setAutowired(true)
                ->setAutoconfigured(true);

            // resolve options set for the data source
            $callable = [$supportedTypes[$type], 'resolveOptions'];
            try {
                $options = $callable($configuredDataSource['options'] ?? []);
            } catch (OptionsResolverException $resolverException) {
                throw new \LogicException(sprintf('Options issue at "sigwin_yassg.data_sources.%1$s.options": %2$s', $name, $resolverException->getMessage()));
            }

            foreach ($options as $key => $value) {
                $definition->setArgument('$'.$key, $value);
            }

            $id = sprintf('sigwin_yassg.data_source.%1$s', $name);
            $container->setDefinition($id, $definition);
            $container->setAlias(sprintf('%1$s $%2$s', DataSource::class, $name), $id);
        }
        $container->setParameter('sigwin_yassg.data_sources_spec', null);
    }
}
