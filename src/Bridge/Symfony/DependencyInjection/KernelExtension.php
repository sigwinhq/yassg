<?php

namespace Sigwin\YASSG\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Sigwin\YASSG\Bridge\Symfony\DependencyInjection\Configuration;

class KernelExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);
        
        $container->setParameter('sigwin_yassg.database', $config['database'] ?? []);
        $container->setParameter('sigwin_yassg.routes', $config['routes'] ?? []);
    }
    
    public function getNamespace(): string
    {
        return 'sigwin_yassg';
    }
}
