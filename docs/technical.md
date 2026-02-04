## What this bundle does

### 1. Automatic service tags
This bundle will tag all implementations of `EntityReader` with `marble.entity_manager.entity_reader`,
all implementations of `EntityWriter` with `marble.entity_manager.entity_writer` and all extensions
of `CustomRepository` with `marble.entity_manager.custom_repository`.

### 2. Custom repository auto-wiring
In every `CustomRepository` subclass, it will look for a class-level docblock and try to find an
`@extends CustomRepository<SomeEntity>` annotation. If found, it will configure the container to inject
the entity class name into the constructor of the custom repository. No need for any additional service 
configuration; you can then simply type-hint the custom repository wherever you need it.

### 3. Custom repository service locator
Custom repositories with the `@extends` annotation are collected into a service locator 
`marble.entity_manager.custom_repository_locator`, keyed by the entity class name from the docblock annotation.

### 4. Default `EntityIoProvider`
The `DefaultEntityIoProvider` is based on the tagged `EntityReader` implementations and the
custom repository service locator. It assumes you want to use a single `EntityWriter` implementation
which you have registered in the service container under the interface name.

### 5. Entity manager auto-wiring
The bundle registers the `marble.entity_manager.entity_manager` service into the service container.
Simply type-hint `Marble\EntityManager\EntityManager` in your service constructor or controller method,
and the container will inject it.
