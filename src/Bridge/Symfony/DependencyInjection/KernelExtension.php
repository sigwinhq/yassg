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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class KernelExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sigwin_yassg.database', $config['database'] ?? []);
        $container->setParameter('sigwin_yassg.routes', $config['routes'] ?? []);
    }

    public function prepend(ContainerBuilder $container): void
    {
        /** @var string $templateDir */
        $templateDir = $container->getParameter('sigwin_yassg.template_dir');
        $bundlesTemplateDir = $templateDir.'/bundles';

        if (file_exists($bundlesTemplateDir)) {
            $finder = new Finder();
            $finder
                ->depth('== 0')
                ->in($bundlesTemplateDir);

            $paths = [];
            foreach ($finder->directories() as $directory) {
                $paths[$directory->getRealPath()] = str_replace('Bundle', '', $directory->getFilename());
            }

            $container->prependExtensionConfig('twig', [
                'paths' => $paths,
            ]);
        }
    }

    public function getNamespace(): string
    {
        return 'sigwin_yassg';
    }
}
