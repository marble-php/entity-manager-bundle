<?php

declare(strict_types=1);

namespace Marble\EntityManager\Bundle\Tests;

use Marble\Entity\Entity;
use Marble\Entity\Identifier;
use Marble\Entity\SimpleId;
use Marble\EntityManager\Bundle\DefaultEntityIoProvider;
use Marble\EntityManager\Bundle\Tests\Fixture\TestEntity;
use Marble\EntityManager\Contract\EntityReader;
use Marble\EntityManager\Contract\EntityWriter;
use Marble\EntityManager\Read\DataCollector;
use Marble\EntityManager\Read\ReadContext;
use Marble\EntityManager\Write\DeleteContext;
use Marble\EntityManager\Write\Persistable;
use Marble\EntityManager\Write\WriteContext;
use Marble\Exception\LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class DefaultEntityIoProviderTest extends TestCase
{
    public function testGetReaderReturnsReaderRegisteredForEntityAncestor(): void
    {
        $reader = new class implements EntityReader {
            public static function getEntityClassName(): string
            {
                return TestEntity::class;
            }

            public function read(?object $query, DataCollector $dataCollector, ReadContext $context): void
            {
            }
        };

        $provider = new DefaultEntityIoProvider(
            new ArrayContainer([TestEntity::class => $reader]),
            new ArrayContainer(),
            new ArrayContainer(),
        );

        self::assertSame($reader, $provider->getReader(DefaultEntityIoProviderChildEntity::class));
    }

    public function testGetReaderReturnsNullWhenNoReaderIsRegistered(): void
    {
        $provider = new DefaultEntityIoProvider(
            new ArrayContainer(),
            new ArrayContainer(),
            new ArrayContainer(),
        );

        self::assertNull($provider->getReader(TestEntity::class));
    }

    public function testGetReaderThrowsWhenRegisteredServiceIsNotAReader(): void
    {
        $provider = new DefaultEntityIoProvider(
            new ArrayContainer([TestEntity::class => new \stdClass()]),
            new ArrayContainer(),
            new ArrayContainer(),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('does not implement');

        $provider->getReader(TestEntity::class);
    }

    public function testGetReaderThrowsWhenReaderReportsWrongEntityClass(): void
    {
        $reader = new class implements EntityReader {
            public static function getEntityClassName(): string
            {
                return DefaultEntityIoProviderOtherEntity::class;
            }

            public function read(?object $query, DataCollector $dataCollector, ReadContext $context): void
            {
            }
        };

        $provider = new DefaultEntityIoProvider(
            new ArrayContainer([TestEntity::class => $reader]),
            new ArrayContainer(),
            new ArrayContainer(),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('reads ' . DefaultEntityIoProviderOtherEntity::class . ' instead');

        $provider->getReader(TestEntity::class);
    }

    public function testGetWriterReturnsWriterRegisteredForEntityAncestor(): void
    {
        $writer = new class implements EntityWriter {
            public function write(Persistable $persistable, WriteContext $context): void
            {
            }

            public function delete(Entity $entity, DeleteContext $context): void
            {
            }
        };

        $provider = new DefaultEntityIoProvider(
            new ArrayContainer(),
            new ArrayContainer([TestEntity::class => $writer]),
            new ArrayContainer(),
        );

        self::assertSame($writer, $provider->getWriter(DefaultEntityIoProviderChildEntity::class));
    }

    public function testGetWriterReturnsNullWhenNoWriterIsRegistered(): void
    {
        $provider = new DefaultEntityIoProvider(
            new ArrayContainer(),
            new ArrayContainer(),
            new ArrayContainer(),
        );

        self::assertNull($provider->getWriter(TestEntity::class));
    }

    public function testGetWriterThrowsWhenRegisteredServiceIsNotAWriter(): void
    {
        $provider = new DefaultEntityIoProvider(
            new ArrayContainer(),
            new ArrayContainer([TestEntity::class => new \stdClass()]),
            new ArrayContainer(),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('does not implement');

        $provider->getWriter(TestEntity::class);
    }

    public function testGetCustomRepositoryReturnsNullWhenNoRepositoryIsRegistered(): void
    {
        $provider = new DefaultEntityIoProvider(
            new ArrayContainer(),
            new ArrayContainer(),
            new ArrayContainer(),
        );

        self::assertNull($provider->getCustomRepository(TestEntity::class));
    }

    public function testGetCustomRepositoryThrowsWhenRegisteredServiceIsNotACustomRepository(): void
    {
        $provider = new DefaultEntityIoProvider(
            new ArrayContainer(),
            new ArrayContainer(),
            new ArrayContainer([TestEntity::class => new \stdClass()]),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('does not extend');

        $provider->getCustomRepository(TestEntity::class);
    }

    public function testThrowsWhenClassDoesNotExist(): void
    {
        $provider = new DefaultEntityIoProvider(
            new ArrayContainer(),
            new ArrayContainer(),
            new ArrayContainer(),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unknown class');

        $provider->getReader('Missing\Entity');
    }

    public function testThrowsWhenClassIsNotAnEntity(): void
    {
        $provider = new DefaultEntityIoProvider(
            new ArrayContainer(),
            new ArrayContainer(),
            new ArrayContainer(),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('does not implement');

        $provider->getReader(\stdClass::class);
    }
}

final class ArrayContainer implements ContainerInterface
{
    /**
     * @param array<string, mixed> $services
     */
    public function __construct(
        private readonly array $services = [],
    ) {
    }

    public function get(string $id): mixed
    {
        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }
}

class DefaultEntityIoProviderChildEntity extends TestEntity
{
}

class DefaultEntityIoProviderOtherEntity implements Entity
{
    public function getId(): ?Identifier
    {
        return new SimpleId('2');
    }
}
