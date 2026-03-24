<?php

namespace Marble\EntityManager\Bundle\Tests\Fixture;

use Marble\Entity\Entity;
use Marble\EntityManager\Contract\EntityWriter;
use Marble\EntityManager\Write\DeleteContext;
use Marble\EntityManager\Write\Persistable;
use Marble\EntityManager\Write\WriteContext;

/**
 * @implements EntityWriter<TestEntity>
 */
class TestWriter implements EntityWriter
{
    public function write(Persistable $persistable, WriteContext $context): void
    {
        //
    }

    public function delete(Entity $entity, DeleteContext $context): void
    {
        //
    }
}
