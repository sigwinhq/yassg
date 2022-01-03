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

namespace Sigwin\YASSG\Bridge\Symfony;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

final class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
    use MicroKernelTrait;

    private string $baseDir;

    public function __construct(string $baseDir, string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        $this->baseDir = $baseDir;
    }

    public function getCacheDir(): string
    {
        return $this->baseDir.'/cache';
    }

    public function build(ContainerBuilder $container): void
    {
        $container->setParameter('kernel.secret', uniqid(__DIR__));
        $container->setParameter('sigwin_yassg.base_dir', $this->baseDir);

        $tagged = $container->findTaggedServiceIds('console.command');

        $container->registerExtension(new \Sigwin\YASSG\Bridge\Symfony\DependencyInjection\KernelExtension());
        $container->addCompilerPass(new \Sigwin\YASSG\Bridge\Symfony\DependencyInjection\CompilerPass\RemoveCommandsCompilerPass());
    }

    private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $configDir = $this->getConfigDir();
        $container->import($configDir.'/{packages}/*.yaml');
        $container->import($configDir.'/services.yaml');

        $container->import($this->baseDir.'/{config}/*.yaml');
    }
}
