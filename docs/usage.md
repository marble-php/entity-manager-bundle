## How to use

Please refer to the [Marble Entity Manager documentation](https://github.com/marble-php/entity-manager)
for more information on the roles and contracts of readers, writers and custom repositories.

After installing the bundle, you're ready to start using Marble Entity Manager in your Symfony application.
Just go ahead and implement your readers, and a writer. As long as you make sure these services are registered
in the container, the bundle will collect them into the `DefaultEntityIoProvider`. 
You can then inject the `EntityManager` into your controllers and services to fetch and persist entities.

### Multiple writers

If you want to use more than one writer (e.g. a different one per entity, just like readers),
you will have implement a new `EntityIoProvider`. An easy way to do so is to extend `DefaultEntityIoProvider` 
and override the `getWriter()` method. Register your service in the container
under the interface alias, so that the bundle can inject it:

```yaml
# config/services.yaml

Marble\EntityManager\Contract\EntityIoProvider: '@App\Infrastructure\EntityIoProvider'
```

### Custom repositories

If you want to use custom repositories, just extend the `CustomRepository` class, annotate it with
an `@extends` annotation (see example below), and the bundle will take care of the rest. You can then type-hint
your custom repositories wherever you need them.

```php
/**
 * @extends CustomRepository<SomeEntity>
 */
class SomeEntityRepository extends CustomRepository
```

