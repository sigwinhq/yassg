<?php

declare(strict_types=1);

/*
 * This file is part of the Sigwin Yassg project.
 *
 * (c) sigwin.hr
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sigwin\YASSG\Bridge\Symfony;

use Sigwin\YASSG\Bridge\Symfony\DependencyInjection\CompilerPass\ConfigureDatabasesCompilerPass;
use Sigwin\YASSG\Bridge\Symfony\DependencyInjection\CompilerPass\InjectRouteVariablesCompilerPass;
use Sigwin\YASSG\Bridge\Symfony\DependencyInjection\CompilerPass\RemoveCommandsCompilerPass;
use Sigwin\YASSG\Bridge\Symfony\DependencyInjection\KernelExtension;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

final class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
    use MicroKernelTrait;

    /**
     * @param list<string> $skipBundles
     */
    public function __construct(private readonly string $baseDir, string $environment, bool $debug, private readonly array $skipBundles = [])
    {
        parent::__construct($environment, $debug);
    }

    #[\Override]
    public function getCacheDir(): string
    {
        return $this->baseDir.'/var/cache/'.$this->environment;
    }

    #[\Override]
    public function getLogDir(): string
    {
        return $this->baseDir.'/var/log';
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
        $container->addCompilerPass(new ConfigureDatabasesCompilerPass());
        $container->addCompilerPass(new RemoveCommandsCompilerPass());
        $container->addCompilerPass(new InjectRouteVariablesCompilerPass());
    }

    public function registerBundles(): iterable
    {
        yield from $this->createEnvironmentClasses($this->getBundlesPath(), $this->skipBundles);

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

    /**
     * @param list<string> $skipBundles
     */
    private function createEnvironmentClasses(string $path, array $skipBundles = []): iterable
    {
        if (is_file($path) && \in_array(realpath($path), get_included_files(), true) === false) {
            /**
             * @var array<class-string, array<string, bool>> $classes
             */
            $classes = require $path;
            foreach ($classes as $class => $envs) {
                if (($envs[$this->environment] ?? $envs['all'] ?? false) && ! \in_array($class, $skipBundles, true)) {
                    yield new $class();
                }
            }
        }
    }
}
