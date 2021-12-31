<?php

namespace Sigwin\YASSG\Bridge\Symfony;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class Kernel extends \Symfony\Component\HttpKernel\Kernel
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
        return $this->baseDir .'/cache';
    }

    public function build(ContainerBuilder $container): void
    {
        $container->setParameter('yassg.base_dir', $this->baseDir);
    }
}
