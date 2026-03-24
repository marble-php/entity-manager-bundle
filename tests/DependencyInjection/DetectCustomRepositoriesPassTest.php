<?php

namespace Marble\EntityManager\Bundle\Tests\DependencyInjection;

use Marble\Entity\Entity;
use Marble\EntityManager\Bundle\DependencyInjection\DetectCustomRepositoriesPass;
use Marble\EntityManager\Bundle\Tests\Fixture\RepoWithBadTag1;
use Marble\EntityManager\Bundle\Tests\Fixture\RepoWithBadTag2;
use Marble\EntityManager\Bundle\Tests\Fixture\RepoWithBadTag3;
use Marble\EntityManager\Bundle\Tests\Fixture\TestEntity;
use Marble\EntityManager\Bundle\Tests\Fixture\TestRepository;
use Marble\EntityManager\EntityManager;
use Marble\Exception\LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Marble\EntityManager\Repository\CustomRepository;
use Marble\EntityManager\Repository\Repository;

class DetectCustomRepositoriesPassTest extends MockeryTestCase
{
    public function testLocatorHasTaggedRepository(): void
    {
        $container = new ContainerBuilder();
        $container
            ->register('test_repo', TestRepository::class)
            ->addTag('marble.entity_manager.custom_repository')
            ->setArgument(0, Mockery::mock(EntityManager::class)); // the other arg will be set by our compiler pass

        $pass = new DetectCustomRepositoriesPass();
        $pass->process($container);

        $container->getDefinition('marble.entity_manager.custom_repository_locator')->setPublic(true); // so we can get() the service
        $container->compile();

        $this->assertTrue($container->has('marble.entity_manager.custom_repository_locator'));

        $locator = $container->get('marble.entity_manager.custom_repository_locator');

        $this->assertInstanceOf(ServiceLocator::class, $locator);
        $this->assertCount(1, $locator);
        $this->assertFalse($locator->has(TestRepository::class));
        $this->assertTrue($locator->has(TestEntity::class));

        $repo = $locator->get(TestEntity::class);

        $this->assertInstanceOf(TestRepository::class, $repo);

        // Now check that the repo has the correct entity class constructor-injected.
        $this->assertEquals(TestEntity::class, $repo->getEntityClassName());
    }

    public function testLocatorHasTaggedRepositoryWithInterfaceDocBlock(): void
    {
        $container = new ContainerBuilder();
        $container
            ->register('test_repo', TestRepositoryWithInterface::class)
            ->addTag('marble.entity_manager.custom_repository')
            ->setArgument(0, Mockery::mock(EntityManager::class));

        $pass = new DetectCustomRepositoriesPass();
        $pass->process($container);

        $this->assertTrue($container->hasDefinition('marble.entity_manager.custom_repository_locator'));

        $locatorDef = $container->getDefinition('marble.entity_manager.custom_repository_locator');
        $services = $locatorDef->getArgument(0);

        $this->assertArrayHasKey(TestEntity::class, $services);
    }

    public function testLocatorCreatedEvenWithoutRepositories(): void
    {
        $container = new ContainerBuilder();

        $pass = new DetectCustomRepositoriesPass();
        $pass->process($container);

        $container->getDefinition('marble.entity_manager.custom_repository_locator')->setPublic(true); // so we can get() the service
        $container->compile();

        $this->assertTrue($container->has('marble.entity_manager.custom_repository_locator'));

        $locator = $container->get('marble.entity_manager.custom_repository_locator');

        $this->assertInstanceOf(ServiceLocator::class, $locator);
        $this->assertCount(0, $locator);
    }

    public function testTagOnlyEffectiveOnCustomRepository(): void
    {
        $container = new ContainerBuilder();
        $container
            ->register('test_service', TestEntity::class) // let's pretend this is a service
            ->addTag('marble.entity_manager.custom_repository');

        $pass = new DetectCustomRepositoriesPass();
        $pass->process($container); // should ignore any tagged classes that are not CustomRepository subclasses

        $container->getDefinition('marble.entity_manager.custom_repository_locator')->setPublic(true); // so we can get() the service
        $container->compile();

        $this->assertTrue($container->has('marble.entity_manager.custom_repository_locator'));

        $locator = $container->get('marble.entity_manager.custom_repository_locator');

        $this->assertInstanceOf(ServiceLocator::class, $locator);
        $this->assertCount(0, $locator);
        $this->assertFalse($locator->has(TestRepository::class));
        $this->assertFalse($locator->has(TestEntity::class));
    }

    public function testTagMustHaveOneTypeArgument(): void
    {
        $container = new ContainerBuilder();
        $container
            ->register(RepoWithBadTag1::class, RepoWithBadTag1::class)
            ->addTag('marble.entity_manager.custom_repository');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches("/should specify exactly one type argument/");

        $pass = new DetectCustomRepositoriesPass();
        $pass->process($container);
    }

    public function testTagMustHaveAnObjectTypeArgument(): void
    {
        $container = new ContainerBuilder();
        $container
            ->register(RepoWithBadTag1::class, RepoWithBadTag2::class)
            ->addTag('marble.entity_manager.custom_repository');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches("/specifies an invalid type argument/");

        $pass = new DetectCustomRepositoriesPass();
        $pass->process($container);
    }

    public function testTagMustHaveATypeArgumentImplementingEntity(): void
    {
        $container = new ContainerBuilder();
        $container
            ->register(RepoWithBadTag1::class, RepoWithBadTag3::class)
            ->addTag('marble.entity_manager.custom_repository');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches(sprintf("/does not implement the %s interface/", str_replace('\\', '\\\\', Entity::class)));

        $pass = new DetectCustomRepositoriesPass();
        $pass->process($container);
    }
}

/**
 * @extends CustomRepository<TestEntity>
 */
interface TestRepoInterface extends Repository
{
}

class TestRepositoryWithInterface extends CustomRepository implements TestRepoInterface
{
}
