<?php

declare(strict_types=1);

namespace Marble\EntityManager\Bundle\DependencyInjection;

use Marble\EntityManager\Repository\CustomRepository;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @api
 */
class DetectCustomRepositoriesPass extends AbstractDetectMarbleImplementationPass
{
    #[\Override]
    protected function getBaseClass(): string
    {
        return CustomRepository::class;
    }

    #[\Override]
    protected function getServiceTagName(): string
    {
        return 'marble.entity_manager.custom_repository';
    }

    #[\Override]
    protected function getLocatorServiceId(): string
    {
        return 'marble.entity_manager.custom_repository_locator';
    }

    #[\Override]
    protected function modifyServiceDefinition(Definition $definition, string $entityFqcn): void
    {
        // Use the entity class found in the tag as 2nd argument into the constructor.
        $definition->setBindings(['$entityClass' => $entityFqcn]);
    }
}
