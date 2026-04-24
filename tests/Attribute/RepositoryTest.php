<?php

namespace Marble\EntityManager\Bundle\Tests\Attribute;

use Marble\EntityManager\Bundle\Attribute\AutowireRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class RepositoryTest extends MockeryTestCase
{
    public function testCorrectExpression(): void
    {
        $entityClass = 'App\Entity\User';
        $attribute = new AutowireRepository($entityClass);

        $this->assertSame($entityClass, $attribute->entityClass);
        $this->assertSame(
            "service('marble.entity_manager.repository_factory').getRepository('App\Entity\User')",
            (string) $attribute->value,
        );
    }

    public function testThrowsTypeErrorOnInvalidInput(): void
    {
        $this->expectException(\TypeError::class);

        // @psalm-suppress InvalidArgument
        new AutowireRepository(['App\Entity\User']);
    }
}
