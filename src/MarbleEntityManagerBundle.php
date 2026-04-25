<?php

declare(strict_types=1);

namespace Marble\EntityManager\Bundle;

use Marble\EntityManager\Contract\EntityReader;
use Marble\EntityManager\Contract\EntityWriter;
use Marble\EntityManager\Repository\CustomRepository;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class MarbleEntityManagerBundle extends AbstractBundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DependencyInjection\DetectCustomRepositoriesPass());
        $container->addCompilerPass(new DependencyInjection\DetectEntityWritersPass());
    }

    /**
     * @param array<array-key, mixed> $config
     */
    #[\Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(dirname(__DIR__) . '/config/services.yaml');

        $builder->registerForAutoconfiguration(EntityReader::class)->addTag('marble.entity_manager.entity_reader');
        $builder->registerForAutoconfiguration(EntityWriter::class)->addTag('marble.entity_manager.entity_writer');
        $builder->registerForAutoconfiguration(CustomRepository::class)->addTag('marble.entity_manager.custom_repository');
    }
}
