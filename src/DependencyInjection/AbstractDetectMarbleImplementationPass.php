<?php

declare(strict_types=1);

namespace Marble\EntityManager\Bundle\DependencyInjection;

use Marble\Entity\Entity;
use Marble\Exception\LogicException;
use phpDocumentor\Reflection\DocBlock\Tags\Extends_;
use phpDocumentor\Reflection\DocBlock\Tags\Implements_;
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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

abstract class AbstractDetectMarbleImplementationPass implements CompilerPassInterface
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
    final public function process(ContainerBuilder $container): void
    {
        $locatableServices = [];
        $taggedServiceIds  = $container->findTaggedServiceIds($this->getServiceTagName());

        foreach (array_keys($taggedServiceIds) as $id) {
            $definition = $container->getDefinition($id);
            $class      = $definition->getClass();

            if ($class === null || !class_exists($class)) {
                throw new ServiceNotFoundException($id);
            } elseif (!is_a($class, $this->getBaseClass(), true)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            $doc        = $reflection->getDocComment();
            $doc        = $doc === false ? '' : $doc;

            if (!empty($doc)) {
                $entityFqcn = $this->processDocBlock($reflection, $doc);

                if ($entityFqcn !== null) {
                    // Add service to service locator, keyed by entity class.
                    $locatableServices[$entityFqcn] = new Reference($id);
                    // Do something else?
                    $this->modifyServiceDefinition($definition, $entityFqcn);
                }
            }
        }

        $container
            ->register($this->getLocatorServiceId(), ServiceLocator::class)
            ->addArgument($locatableServices)
            ->addTag('container.service_locator');
    }

    private function processDocBlock(ReflectionClass $class, string $doc): ?string
    {
        $context = $this->contextFactory()->createFromReflector($class);
        $parsed  = $this->docBlockFactory()->create($doc, $context);
        $tags    = $parsed->getTags();

        foreach ($tags as $tag) {
            if (!$tag instanceof Extends_ && !$tag instanceof Implements_) {
                continue; // we're only interested in @extends, @template-extends, @implements or @template-implements
            }

            $type = $tag->getType();

            if (!$type instanceof Generic) {
                continue; // not what we're looking for
            }

            $parent = $this->typeResolver()->resolve((string) $type->getFqsen(), $context);

            if (!($parent instanceof Object_ && is_a((string) $parent, $this->getBaseClass(), true))) {
                continue; // not what we're looking for
            }

            $templates = $type->getTypes();

            if (count($templates) <> 1) {
                throw new LogicException(sprintf(
                    "The @%s tag of %s should specify exactly one type argument to %s, e.g. %s<ExampleEntity>; %s found.",
                    $tag->getName(),
                    $class->getName(),
                    $short = (new ReflectionClass($this->getBaseClass()))->getShortName(),
                    $short,
                    (string) $type
                ));
            }

            $template = reset($templates);

            assert($template instanceof Type);

            if (!$template instanceof Object_) {
                throw new LogicException(sprintf(
                    "The @%s tag of %s specifies an invalid type argument to %s (%s is a %s).",
                    $tag->getName(),
                    $class->getName(),
                    (new ReflectionClass($this->getBaseClass()))->getShortName(),
                    (string) $template,
                    $template::class
                ));
            }

            $resolvedType = (string) $this->typeResolver()->resolve((string) $template->getFqsen(), $context);

            if (!is_a($resolvedType, Entity::class, true)) {
                throw new LogicException(sprintf(
                    "Type argument %s in the @%s tag of %s does not implement the %s interface.",
                    $resolvedType,
                    $tag->getName(),
                    $class->getName(),
                    Entity::class
                ));
            }

            return ltrim($resolvedType, '\\');
        }

        return null;
    }

    /**
     * @return class-string
     */
    abstract protected function getBaseClass(): string;

    abstract protected function getServiceTagName(): string;

    abstract protected function getLocatorServiceId(): string;

    protected function modifyServiceDefinition(Definition $definition, string $entityFqcn): void
    {
    }
}
