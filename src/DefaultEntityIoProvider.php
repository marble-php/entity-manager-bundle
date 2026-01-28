<?php

namespace Marble\EntityManager\Bundle;

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
    public function __construct(
        private readonly ContainerInterface $readers,
        private readonly EntityWriter $writer, // acceptable if only 1 implementation exists
        private readonly ContainerInterface $repositories,
    ) {
    }

    /**
     * @template T
     * @param class-string<T> $className
     * @return EntityReader<T>|null
     * @throws ContainerExceptionInterface
     */
    #[\Override]
    public function getReader(string $className): ?EntityReader
    {
        $reader = $this->readers->get($className);

        if ($reader !== null && !$reader instanceof EntityReader) {
            throw new LogicException(sprintf("Reader %s for entity %s does not implement %s.",
                get_debug_type($reader), $className, EntityReader::class));
        }

        return $reader;
    }

    #[\Override]
    public function getWriter(string $className): ?EntityWriter
    {
        return $this->writer;
    }

    /**
     * @template T
     * @param class-string<T> $className
     * @return CustomRepository<T>|null
     * @throws ContainerExceptionInterface
     */
    #[\Override]
    public function getCustomRepository(string $className): CustomRepository|null
    {
        $repository = $this->repositories->get($className);

        if ($repository !== null && !$repository instanceof CustomRepository) {
            throw new LogicException(sprintf("Custom repository %s for entity %s does not extend %s.",
                get_debug_type($repository), $className, CustomRepository::class));
        }

        return $repository;
    }
}
