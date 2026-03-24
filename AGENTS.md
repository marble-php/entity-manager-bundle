# AI instructions

Testing tools are PhpUnit and Mockery. All tests must extend `MockeryTestCase`. 
Place tests in the `/tests` directory. Mirror source code namespaces.
To run tests, run `_ vendor/bin/phpunit tests`. 
(The `_` prefix is a custom alias for `docker-compose exec php` to run commands inside the PHP container.)
