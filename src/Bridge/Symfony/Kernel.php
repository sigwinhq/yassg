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

use Sigwin\YASSG\Bridge\Symfony\DependencyInjection\CompilerPass\RemoveCommandsCompilerPass;
use Sigwin\YASSG\Bridge\Symfony\DependencyInjection\KernelExtension;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
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
        return $this->baseDir.'/cache/'.$this->environment;
    }

    public function build(ContainerBuilder $container): void
    {
        $container->setParameter('kernel.secret', uniqid(__DIR__, true));
        $container->setParameter('sigwin_yassg.base_dir', $this->baseDir);

        // TODO: make configurable
        $container->setParameter('sigwin_yassg.asset_dir', $this->baseDir.'/public/assets');
        $container->setParameter('sigwin_yassg.build_dir', $this->baseDir.'/public');
        $container->setParameter('sigwin_yassg.template_dir', $this->baseDir.'/templates');
        $container->setParameter('sigwin_yassg.translation_dir', $this->baseDir.'/translations');

        $container->registerExtension(new KernelExtension());
        $container->addCompilerPass(new RemoveCommandsCompilerPass());
    }

    public function registerBundles(): iterable
    {
        yield from $this->createEnvironmentClasses($this->getBundlesPath());

        yield from $this->createEnvironmentClasses($this->baseDir.'/config/bundles.php');
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $configDir = $this->getConfigDir();
        $container->import($configDir.'/{packages}/*.yaml');
        $container->import($configDir.'/services.yaml');

        $container->import($this->baseDir.'/{config}/*.yaml');
        $container->import($this->baseDir.'/{config}/{packages}/*.yaml');
    }

    private function createEnvironmentClasses(string $path): iterable
    {
        if (file_exists($path)) {
            $classes = require $path;
            foreach ($classes as $class => $envs) {
                if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                    yield new $class();
                }
            }
        }
    }
}
