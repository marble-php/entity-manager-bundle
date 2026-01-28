<?php

namespace Marble\EntityManager\Bundle\Tests\Fixture;

use Marble\Entity\Entity;
use Marble\Entity\Identifier;
use Marble\Entity\SimpleId;

class TestEntity implements Entity
{
    public function getId(): ?Identifier
    {
        return new SimpleId(1);
    }
}
