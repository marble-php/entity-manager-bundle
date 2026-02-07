<?php

namespace Marble\EntityManager\Bundle;

use Marble\Entity\Entity;
use Marble\EntityManager\Contract\EntityIoProvider;
use Marble\EntityManager\Contract\EntityReader;
use Marble\EntityManager\Contract\EntityWriter;
use Marble\EntityManager\Repository\CustomRepository;
use Marble\Exception\LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * @api
 * @noinspection PhpClassCanBeReadonlyInspection
 */
class DefaultEntityIoProvider implements EntityIoProvider
{
    /**
     * @param ContainerInterface        $readers
     * @param EntityWriter<Entity>|null $writer
     * @param ContainerInterface        $repositories
     */
    public function __construct(
        private readonly ContainerInterface $readers,
        private readonly ?EntityWriter      $writer, // acceptable if only 1 implementation exists
        private readonly ContainerInterface $repositories,
    ) {
    }

    /**
     * @template T of Entity
     * @param class-string<T> $className
     * @return EntityReader<T>|null
     * @throws ContainerExceptionInterface
     */
    #[\Override]
    public function getReader(string $className): ?EntityReader
    {
        $this->validateEntityClass($className);

        /** @psalm-suppress MixedAssignment */
        $reader = $this->readers->get($className);

        if ($reader === null) {
            return null;
        } elseif (!$reader instanceof EntityReader) {
            throw new LogicException(sprintf("Reader %s for entity %s does not implement %s.",
                get_debug_type($reader), $className, EntityReader::class));
        } elseif (!is_a($className, $readerEntityClass = $reader::getEntityClassName(), true)) {
            throw new LogicException(sprintf("Reader %s returned for entity %s reads %s instead.",
                $reader::class, $className, $readerEntityClass));
        }

        /** @var EntityReader<T> $reader */
        return $reader;
    }

    /**
     * @template T of Entity
     * @param class-string<T> $className
     * @return EntityWriter<T>
     */
    #[\Override]
    public function getWriter(string $className): EntityWriter
    {
        if ($this->writer === null) {
            throw new LogicException(sprintf('No service implementing "%s" was found in the service container. '
                . 'Please register one to use Marble with the default IO provider.', EntityWriter::class));
        }

        /** @var EntityWriter<T> $writer */
        $writer = $this->writer;

        return $writer;
    }

    /**
     * @template T of Entity
     * @param class-string<T> $className
     * @return CustomRepository<T>|null
     * @throws ContainerExceptionInterface
     */
    #[\Override]
    public function getCustomRepository(string $className): CustomRepository|null
    {
        $this->validateEntityClass($className);

        $repository = $this->repositories->get($className);

        if ($repository !== null && !$repository instanceof CustomRepository) {
            throw new LogicException(sprintf("Custom repository %s for entity %s does not extend %s.",
                get_debug_type($repository), $className, CustomRepository::class));
        }

        /** @var CustomRepository<T> $repository */
        return $repository;
    }

    private function validateEntityClass(string $className): void
    {
        if (!class_exists($className)) {
            throw new LogicException(sprintf("Unknown class %s passed to %s.",
                $className, __METHOD__));
        } elseif (!is_subclass_of($className, Entity::class)) {
            throw new LogicException(sprintf("Class %s passed to %s does not implement the %s interface.",
                $className, __METHOD__, Entity::class));
        }
    }
}
