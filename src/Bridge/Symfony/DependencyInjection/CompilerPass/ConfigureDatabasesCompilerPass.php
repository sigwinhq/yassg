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

use Sigwin\YASSG\Database;
use Sigwin\YASSG\Storage;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface as OptionsResolverException;

final class ConfigureDatabasesCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        $supportedStorageTypes = [];
        $references = $this->findAndSortTaggedServices('sigwin_yassg.abstract.data_source_type', $container);
        foreach ($references as $reference) {
            $reference = $reference->__toString();
            $storageDefinition = $container->getDefinition($reference);

            /** @var class-string<Storage> $class */
            $class = $storageDefinition->getClass();
            $callable = [$class, 'getType'];
            $type = $callable();

            if (isset($supportedStorageTypes[$type])) {
                throw new \LogicException(sprintf('Data source type %1$s already provided by %2$s', $type, $reference));
            }

            $supportedStorageTypes[$type] = $class;
            $container->removeDefinition($reference);
        }
        unset($type);

        /** @var array<string, array{storage: string, options?: array}> $configuredDataSources */
        $configuredDataSources = $container->getParameter('sigwin_yassg.databases_spec');
        foreach ($configuredDataSources as $name => $configuredDataSource) {
            $type = $configuredDataSource['storage'];

            if (\array_key_exists($type, $supportedStorageTypes) === false) {
                throw new \LogicException(sprintf('Unsupported type "%1$s" at "sigwin_yassg.data_sources.%2$s", allowed values: %3$s', $type, $name, implode(', ', array_keys($supportedStorageTypes))));
            }

            $storageDefinition = new Definition($supportedStorageTypes[$type]);
            $storageDefinition
                ->setAutowired(true)
                ->setAutoconfigured(true);

            // resolve options set for the data source
            $callable = [$supportedStorageTypes[$type], 'resolveOptions'];
            try {
                $options = $callable($configuredDataSource['options'] ?? []);
            } catch (OptionsResolverException $resolverException) {
                throw new \LogicException(sprintf('Options issue at "sigwin_yassg.data_sources.%1$s.options": %2$s', $name, $resolverException->getMessage()));
            }

            foreach ($options as $key => $value) {
                $storageDefinition->setArgument('$'.$key, $value);
            }

            $storageId = sprintf('sigwin_yassg.database.storage.%1$s', $name);
            $container->setDefinition($storageId, $storageDefinition);

            $databaseDefinition = new Definition(Database::class);
            $databaseDefinition
                ->setAutowired(true)
                ->setAutoconfigured(true);
            $databaseDefinition
                ->setArgument(0, new Reference($storageId));

            $container->setDefinition(sprintf('sigwin_yassg.database.%1$s', $name), $databaseDefinition);
        }
        $container->setParameter('sigwin_yassg.databases_spec', null);
    }
}
