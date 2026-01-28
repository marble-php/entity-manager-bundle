<?php

namespace Marble\EntityManager\Bundle\Tests\Fixture;

use Marble\EntityManager\Contract\EntityReader;
use Marble\EntityManager\Read\DataCollector;
use Marble\EntityManager\Read\ReadContext;

class TestReader implements EntityReader
{
    public static function getEntityClassName(): string
    {
        return TestEntity::class;
    }

    public function read(?object $query, DataCollector $dataCollector, ReadContext $context): void
    {
        //
    }
}
