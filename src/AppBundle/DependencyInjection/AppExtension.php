<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AppExtension extends Extension
{
    /**
     * Called by the kernel at load-time.
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        /*@var XmlFileLoader*/
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('params.yml');
        $loader->load('services.yml');
    }
}
