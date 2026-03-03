<?php

namespace Marble\EntityManager\Bundle\Tests\DependencyInjection;

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
        $this->assertTrue($locator->has(TestEntity::class));

        $writer = $locator->get(TestEntity::class);
        $this->assertInstanceOf(TestWriterWithTag::class, $writer);
    }
}

/**
 * @implements EntityWriter<TestEntity>
 */
class TestWriterWithTag extends TestWriter
{
}
