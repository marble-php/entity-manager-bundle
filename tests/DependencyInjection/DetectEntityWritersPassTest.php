<?php

namespace Marble\EntityManager\Bundle\Tests\DependencyInjection;

use Marble\Entity\Entity;
use Marble\Entity\Identifier;
use Marble\EntityManager\Bundle\DependencyInjection\DetectEntityWritersPass;
use Marble\EntityManager\Bundle\Tests\Fixture\TestEntity;
use Marble\EntityManager\Bundle\Tests\Fixture\TestWriter;
use Marble\EntityManager\Contract\EntityWriter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ServiceLocator;

class DetectEntityWritersPassTest extends MockeryTestCase
{
    public function testLocatorHasTaggedWriter(): void
    {
        $container = new ContainerBuilder();
        $container
            ->register('test_writer', TestWriterWithTag::class)
            ->addTag('marble.entity_manager.entity_writer');

        $pass = new DetectEntityWritersPass();
        $pass->process($container);

        $container->getDefinition('marble.entity_manager.entity_writer_locator')->setPublic(true);
        $container->compile();

        $this->assertTrue($container->has('marble.entity_manager.entity_writer_locator'));

        $locator = $container->get('marble.entity_manager.entity_writer_locator');

        $this->assertInstanceOf(ServiceLocator::class, $locator);
        $this->assertCount(1, $locator);
        $this->assertTrue($locator->has(AnotherEntity::class));
        $this->assertFalse($locator->has(TestEntity::class));

        $writer = $locator->get(AnotherEntity::class);
        $this->assertInstanceOf(TestWriterWithTag::class, $writer);
    }

    public function testLocatorHasTaggedWriterWithInterfaceDocBlock(): void
    {
        $container = new ContainerBuilder();
        $container
            ->register('test_writer', TestWriterWithInterface::class)
            ->addTag('marble.entity_manager.entity_writer');

        $pass = new DetectEntityWritersPass();
        $pass->process($container);

        $container->getDefinition('marble.entity_manager.entity_writer_locator')->setPublic(true);
        $container->compile();

        $this->assertTrue($container->hasDefinition('marble.entity_manager.entity_writer_locator'));

        $locator = $container->get('marble.entity_manager.entity_writer_locator');

        $this->assertInstanceOf(ServiceLocator::class, $locator);
        $this->assertCount(1, $locator);
        $this->assertTrue($locator->has(AnotherEntity::class));
        $this->assertFalse($locator->has(TestEntity::class));

        $writer = $locator->get(AnotherEntity::class);
        $this->assertInstanceOf(TestWriterWithInterface::class, $writer);
    }
}

/**
 * @implements EntityWriter<AnotherEntity>
 */
interface TestWriterInterface extends EntityWriter
{
}

class TestWriterWithInterface extends TestWriter implements TestWriterInterface
{
}

/**
 * @implements EntityWriter<AnotherEntity>
 */
class TestWriterWithTag extends TestWriter
{
}

class AnotherEntity implements Entity
{
    public function getId(): ?Identifier
    {
        return null;
    }
}
