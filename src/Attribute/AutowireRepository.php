<?php

namespace Marble\EntityManager\Bundle\Attribute;

use Marble\Entity\Entity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @api
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
class AutowireRepository extends Autowire
{
    /**
     * @param class-string<Entity> $entityClass
     */
    public function __construct(
        public readonly string $entityClass,
    ) {
        // https://symfony.com/blog/new-in-symfony-6-3-dependency-injection-improvements#allow-extending-the-autowire-attribute
        $expression = \sprintf("service('%s').getRepository(service('%s'), '%s')",
            'marble.entity_manager.repository_factory', 'marble.entity_manager.entity_manager', str_replace('\\', '\\\\', $entityClass),
        );

        parent::__construct(expression: $expression);
    }
}
