<?php

namespace Sigwin\YASSG\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class KernelExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // TODO: validate config here
        $container->setParameter('sigwin_yassg.routes', $configs[0]['routes']);
    }
    
    public function getNamespace(): string
    {
        return 'sigwin_yassg';
    }
}
