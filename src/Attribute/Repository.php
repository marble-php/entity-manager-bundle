<?php

namespace Marble\EntityManager\Bundle\Attribute;

use Marble\Entity\Entity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Repository extends Autowire
{
    /**
     * @param class-string<Entity> $entityClass
     */
    public function __construct(
        public readonly string $entityClass,
    ) {
        // https://symfony.com/blog/new-in-symfony-6-3-dependency-injection-improvements#allow-extending-the-autowire-attribute
        $expression = \sprintf("service('marble.entity_manager.repository_factory').getRepository('%s')", $entityClass);

        parent::__construct(expression: $expression);
    }
}
