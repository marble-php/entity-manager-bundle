<?php

namespace Marble\EntityManager\Bundle\DependencyInjection;

use Marble\Entity\Entity;
use Marble\EntityManager\Repository\CustomRepository;
use Marble\Exception\LogicException;
use phpDocumentor\Reflection\DocBlock\Tags\Extends_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\PseudoTypes\Generic;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use function PHPUnit\Framework\assertInstanceOf;

/**
 * @api
 */
class DetectCustomRepositoriesPass implements CompilerPassInterface
{
    private ?TypeResolver $typeResolver = null;
    private ?ContextFactory $contextFactory = null;
    private ?DocBlockFactoryInterface $docBlockFactory = null;

    private function typeResolver(): TypeResolver
    {
        return $this->typeResolver ??= new TypeResolver();
    }

    private function contextFactory(): ContextFactory
    {
        return $this->contextFactory ??= new ContextFactory();
    }

    private function docBlockFactory(): DocBlockFactoryInterface
    {
        return $this->docBlockFactory ??= DocBlockFactory::createInstance();
    }

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $locatableServices = [];
        $taggedServiceIds  = $container->findTaggedServiceIds('marble.entity_manager.custom_repository');

        foreach (array_keys($taggedServiceIds) as $id) {
            $definition = $container->getDefinition($id);
            $class      = $definition->getClass();

            if ($class === null || !class_exists($class)) {
                throw new ServiceNotFoundException($id);
            } elseif (!is_a($class, CustomRepository::class, true)) {
                continue; // tag is only effective on CustomRepository subclasses
            }

            $reflection = new ReflectionClass($class);
            $doc        = $reflection->getDocComment();
            $doc        = $doc === false ? '' : $doc;

            if (!empty($doc)) {
                $entityFqcn = $this->processDocBlock($reflection, $doc);

                if ($entityFqcn !== null) {
                    // Use the entity class found in the `@extends CustomRepository<SomeEntity>` tag as 2nd argument into the constructor.
                    $definition->setBindings(['$entityClass' => $entityFqcn]);
                    // Add repository to service locator, keyed by entity class.
                    $locatableServices[$entityFqcn] = new Reference($id);
                }
            }
        }

        $container
            ->register('marble.entity_manager.custom_repository_locator', ServiceLocator::class)
            ->addArgument($locatableServices)
            ->addTag('container.service_locator');
    }

    private function processDocBlock(ReflectionClass $class, string $doc): ?string
    {
        $context = $this->contextFactory()->createFromReflector($class);
        $parsed  = $this->docBlockFactory()->create($doc, $context);
        $tags    = $parsed->getTags();

        foreach ($tags as $tag) {
            if (!$tag instanceof Extends_) {
                continue; // we're only interested in @extends or @template-extends
            }

            $type = $tag->getType();

            if (!$type instanceof Generic) {
                continue; // not what we're looking for
            }

            $parent = $this->typeResolver()->resolve((string) $type->getFqsen(), $context);

            if (!($parent instanceof Object_ && is_a((string) $parent, CustomRepository::class, true))) {
                continue; // not what we're looking for
            }

            $templates = $type->getTypes();

            if (count($templates) <> 1) {
                throw new LogicException(sprintf("The @%s tag of %s should specify exactly one type argument to CustomRepository, " .
                    "e.g. CustomRepository<Example" . "Entity>; %s found.", $tag->getName(), $class->getName(), (string) $type));
            }

            $template = reset($templates);

            assert($template instanceof Type);

            if (!$template instanceof Object_) {
                throw new LogicException(sprintf("The @%s tag of %s specifies an invalid type argument to CustomRepository (%s is a %s).",
                    $tag->getName(), $class->getName(), (string) $template, $template::class));
            }

            $resolvedType = (string) $this->typeResolver()->resolve((string) $template->getFqsen(), $context);

            if (!is_a($resolvedType, Entity::class, true)) {
                throw new LogicException(sprintf("Type argument %s in the @%s tag of %s does not implement the %s interface.",
                    $resolvedType, $tag->getName(), $class->getName(), Entity::class));
            }

            return ltrim($resolvedType, '\\');
        }

        return null;
    }
}
