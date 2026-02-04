## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Then, open a command console, enter your project directory and execute:

```console
composer require marble/entity-manager-bundle
```

This will also install `marble/entity-manager`.

If your application uses Symfony Flex, installation is done! Otherwise, enable the bundle by adding
it to the list of registered bundles in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Marble\EntityManager\Bundle\MarbleEntityManagerBundle::class => ['all' => true],
];
```
