<?php

namespace Marble\EntityManager\Bundle\Tests;

use Marble\EntityManager\Bundle\MarbleEntityManagerBundle;
use Marble\EntityManager\Bundle\Tests\Fixture\TestEntity;
use Marble\EntityManager\Bundle\Tests\Fixture\TestReader;
use Marble\EntityManager\Bundle\Tests\Fixture\TestRepository;
use Marble\EntityManager\Bundle\Tests\Fixture\TestWriter;
use Marble\EntityManager\Contract\EntityWriter;
use Marble\EntityManager\EntityManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ServiceLocator;

class MarbleEntityManagerBundleTest extends MockeryTestCase
{
    public function testTaggingAndCompiling(): void
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug'       => true,
            'kernel.environment' => 'test',
            'kernel.build_dir'   => __DIR__ . '/../var/cache/test',
        ]));

        $bundle    = new MarbleEntityManagerBundle();
        $extension = $bundle->getContainerExtension();

        $bundle->build($container);
        $container->registerExtension($extension);
        $container->loadFromExtension($extension->getAlias());

        // Register synthetic definition, so that after container compilation we can just set mock in the container.
        $container->register(EntityManager::class)->setSynthetic(true);
        // Register definitions for dependencies of services that won't be built.
        $container->register(EventDispatcherInterface::class);
        $container->register(EntityWriter::class);

        // Register our implementations and make sure they'll be auto-configured (= tagged).
        $container->register('test_reader', TestReader::class)->setAutoconfigured(true);
        $container->register('test_writer', TestWriter::class)->setAutoconfigured(true)->setPublic(true); // prevent auto-removal
        $container->autowire('test_repo', TestRepository::class)->setAutoconfigured(true); // also auto-wire

        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                // Make sure we can get() this service from the container, after compilation.
                $container->getDefinition('marble.entity_manager.custom_repository_locator')->setPublic(true);
            }
        }, PassConfig::TYPE_BEFORE_REMOVING);

        $container->compile();

        // Make sure mock is injected when container builds the repository service.
        $container->set(EntityManager::class, Mockery::mock(EntityManager::class));

        $tags = $container->findTags();
        $this->assertContains('marble.entity_manager.entity_reader', $tags);
        $this->assertContains('marble.entity_manager.entity_writer', $tags);
        $this->assertContains('marble.entity_manager.custom_repository', $tags);

        $this->assertCount(1, $readers = $container->findTaggedServiceIds('marble.entity_manager.entity_reader'));
        $this->assertCount(1, $writers = $container->findTaggedServiceIds('marble.entity_manager.entity_writer'));
        $this->assertCount(1, $repos = $container->findTaggedServiceIds('marble.entity_manager.custom_repository'));
        $this->assertEquals('test_reader', array_key_first($readers));
        $this->assertEquals('test_writer', array_key_first($writers));
        $this->assertEquals('test_repo', array_key_first($repos));

        $this->assertTrue($container->has('marble.entity_manager.custom_repository_locator'));
        $locator = $container->get('marble.entity_manager.custom_repository_locator');
        $this->assertInstanceOf(ServiceLocator::class, $locator);
        $this->assertTrue($locator->has(TestEntity::class));

        $repo = $locator->get(TestEntity::class);
        $this->assertInstanceOf(TestRepository::class, $repo);
        $this->assertEquals(TestEntity::class, $repo->getEntityClassName());
    }
}
