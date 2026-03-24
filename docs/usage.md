## How to use

Please refer to the [Marble Entity Manager documentation](https://github.com/marble-php/entity-manager)
for more information on the roles and contracts of readers, writers and custom repositories.

After installing the bundle, you're ready to start using Marble Entity Manager in your Symfony application.
Just go ahead and implement your readers and writers. As long as you make sure these services are registered
in the container, the bundle will collect them into the `DefaultEntityIoProvider`. 
You can then inject the `EntityManager` into your controllers and services to fetch and persist entities.

### Custom repositories

Do not use custom repositories to implement custom fetching behaviors.
Instead, create custom query classes that you pass to the existing `fetch*` methods,
and make sure your readers handle them correctly, e.g. adapt their SELECT statements according to 
the query class and its properties.

Given this, you probably want to use custom repositories mainly for more expressive dependency injection.
And you will probably want to type-hint **repository interfaces** instead of concrete classes.

First, create an interface that extends Marble’s `Repository` interface. Add the `@extends` annotation as follows.
This allows the bundle to automatically register

```php
/**
 * @extends Repository<SomeEntity>
 */
interface SomeEntityRepositoryInterface extends Repository
```

The Then create a class that extends `CustomRepository` and implements the interface. 

```php
class SomeEntityRepository extends CustomRepository implements SomeEntityRepositoryInterface
```

You don’t need to add the `@extends` annotation to this class, because the interface already has one, but you may.


If you want to use custom repositories, just extend the `CustomRepository` class, annotate it with
an `@extends` annotation (see example below), and the bundle will take care of the rest. You can then type-hint
your custom repositories wherever you need them.

```php
/**
 * @extends CustomRepository<SomeEntity>
 */
class SomeEntityRepository extends CustomRepository
```

