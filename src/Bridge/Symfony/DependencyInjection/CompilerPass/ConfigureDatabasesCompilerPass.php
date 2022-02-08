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

use Sigwin\YASSG\Bridge\Attribute\Localized;
use Sigwin\YASSG\Bridge\Symfony\Serializer\Normalizer\LocalizingNormalizer;
use Sigwin\YASSG\Database;
use Sigwin\YASSG\Database\MemoryDatabase;
use Sigwin\YASSG\DatabaseProvider;
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
        $references = $this->findAndSortTaggedServices('sigwin_yassg.database.storage.type', $container);
        foreach ($references as $reference) {
            $reference = $reference->__toString();
            $storageDefinition = $container->getDefinition($reference);

            // TODO: error handling
            $tag = current($storageDefinition->getTag('sigwin_yassg.database.storage.type'));
            $type = $tag['type'];

            if (isset($supportedStorageTypes[$type])) {
                throw new \LogicException(sprintf('Data source type %1$s already provided by %2$s', $type, $reference));
            }

            /** @var class-string<Storage> $class */
            $class = $storageDefinition->getClass();

            $supportedStorageTypes[$type] = $class;
            $container->removeDefinition($reference);
        }
        unset($type);

        $databases = [];
        $localizableClasses = [];

        /** @var array<string, array{storage: string, class: class-string, options?: array}> $configuredDatabases */
        $configuredDatabases = $container->getParameter('sigwin_yassg.databases_spec');
        foreach ($configuredDatabases as $name => $database) {
            $type = $database['storage'];
            if (\array_key_exists($type, $supportedStorageTypes) === false) {
                throw new \LogicException(sprintf('Unsupported type "%1$s" at "sigwin_yassg.data_sources.%2$s", allowed values: %3$s', $type, $name, implode(', ', array_keys($supportedStorageTypes))));
            }

            /** @var class-string $databaseClass */
            $databaseClass = ltrim($database['class'], '\\');

            $storageDefinition = new Definition($supportedStorageTypes[$type]);
            $storageDefinition
                ->setAutowired(true)
                ->setAutoconfigured(true);
            $callable = [$supportedStorageTypes[$type], 'resolveOptions'];
            try {
                $options = $callable($database['options'] ?? []);
            } catch (OptionsResolverException $resolverException) {
                throw new \LogicException(sprintf('Options issue at "sigwin_yassg.data_sources.%1$s.options": %2$s', $name, $resolverException->getMessage()));
            }
            foreach ($options as $key => $value) {
                $storageDefinition->setArgument('$'.$key, $value);
            }
            $storageId = sprintf('sigwin_yassg.database.storage.%1$s', $name);
            $container->setDefinition($storageId, $storageDefinition);

            $denormalizerDefinition = new Definition(Storage\DenormalizingStorage::class);
            $denormalizerDefinition->setDecoratedService($storageId);
            $denormalizerDefinition
                ->setArgument(0, new Reference('serializer'))
                ->setArgument(1, new Reference(sprintf('sigwin_yassg.database.storage_denormalizer.%1$s.inner', $name)))
                ->setArgument(2, $databaseClass);
            $container->setDefinition(sprintf('sigwin_yassg.database.storage_denormalizer.%1$s', $name), $denormalizerDefinition);

            $databaseDefinition = new Definition(MemoryDatabase::class);
            $databaseDefinition
                ->setAutowired(true)
                ->setAutoconfigured(true);
            $databaseDefinition
                ->setArgument(0, new Reference($storageId))
                ->setArgument(1, new Reference('sigwin_yassg.expression_language'))
                ->setArgument(2, $this->getProperties($databaseClass));
            $databaseId = sprintf('sigwin_yassg.database.%1$s', $name);
            $container->setDefinition($databaseId, $databaseDefinition);
            $container->setAlias(sprintf('%1$s $%2$s', Database::class, $name), $databaseId);

            $databases[$name] = new Reference($databaseId);

            $localizableProperties = $this->getLocalizableProperties($databaseClass);
            if ($localizableProperties !== []) {
                $localizableClasses[$databaseClass] = $localizableProperties;
            }
        }
        $container->setParameter('sigwin_yassg.databases_spec', null);

        $container
            ->getDefinition(DatabaseProvider::class)
                ->setArgument(0, $databases);

        if ($localizableClasses !== []) {
            $localizingObjectNormalizer = new Definition(LocalizingNormalizer::class);
            $localizingObjectNormalizer
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setDecoratedService('serializer.normalizer.object')
                ->setArgument(0, $localizableClasses);
            $container->setDefinition('sigwin_yassg.normalizer.object_normalizer', $localizingObjectNormalizer);
        }
    }

    /**
     * @param class-string $class
     */
    private function getProperties(string $class): array
    {
        $properties = [];
        $reflection = new \ReflectionClass($class);
        foreach ($reflection->getProperties() as $property) {
            $properties[] = $property->getName();
        }

        return $properties;
    }

    /**
     * @param class-string $class
     */
    private function getLocalizableProperties(string $class): array
    {
        $properties = [];
        $reflection = new \ReflectionClass($class);
        foreach ($reflection->getProperties() as $property) {
            if ($property->getAttributes(Localized::class) !== []) {
                $properties[] = $property->getName();
            }
        }

        return $properties;
    }
}
