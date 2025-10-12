<?php

namespace Marble\EntityManager\Bundle;

use Marble\EntityManager\Contract\EntityReader;
use Marble\EntityManager\Contract\EntityWriter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class MarbleEntityManagerBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $builder
            ->registerForAutoconfiguration(EntityReader::class)
            ->addTag('marble.entity_manager.entity_reader');

        $builder
            ->registerForAutoconfiguration(EntityWriter::class)
            ->addTag('marble.entity_manager.entity_writer');
    }
}
