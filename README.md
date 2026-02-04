# Marble Entity Manager Bundle

![Minimum PHP version](https://img.shields.io/badge/php-v8.2+-8892BF)
![Tests](https://img.shields.io/badge/tests-passing-brightgreen)
![Installs via Packagist](https://img.shields.io/packagist/dt/marble/entity-manager-bundle)
![Latest stable version](https://img.shields.io/packagist/v/marble/entity-manager-bundle)
![License](https://img.shields.io/packagist/l/marble/entity-manager-bundle)

This bundle provides seamless Symfony integration for the [Marble Entity Manager](https://github.com/marble-php/entity-manager) 
library. It takes the hassle out of manual configuration by automatically wiring up your readers, writers, and 
repositories, so you can focus on building your application’s core logic.

- **Zero configuration:** Automatically discovers and tags your readers, writers, and repositories.
- **Smart auto-wiring:** Injects the correct entity class into your custom repositories based on PHPDoc annotations.
- **Developer-friendly:** Full support for IDE autocompletion and static analysis (Psalm/PHPStan).
- **Flexible:** Easily override the default behavior if your project has special requirements.

## Documentation

The source of [the bundle documentation](docs/index.md) is located in the `/docs` directory.

## License

Marble is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## About

Made by [a Dutch guy](https://github.com/mjpvandenberg) in his spare time, with some frustrations 
towards (as well as love for) Doctrine ORM.
