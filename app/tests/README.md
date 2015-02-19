DeskPRO Test Suite
===================

# Getting Started

1. Copy "config.test.php.dist" to "config.test.php"
2. Open "config.test.php" and enter your db details (or just create the default deskpro_test database)

# Running Tests

We use 3 separate tools in the suite. Each can be run individually with their commands:

## PHPSpec

### Create a new spec (this will auto generate the files for you):

`bin/phpspec desc Application/SomeBundle/My/Class`

### Run the phpspec suite:

`bin/phpspec run`

### Run a specific spec

`bin/phpspec run spec/Application/SomeBundle/My/ClassSpec.php`

### Run a whole directory of specs

`bin/phpspec run spec/Application/SomeBundle`

## PHPUnit

### Run the phpunit suite:

`bin/phpunit`

## Behat

### Run the behat suite:

`bin/behat`

### Run a specific feature file:

`bin/behat features/portal/languages.feature`

### Run a specific scenario on a feature file:

Assuming line 15 is the start of a Scenario: block:

`bin/behat features/portal/languages.feature:15`

### Run a folder of features:

`bin/behat features/portal/router`

# Run all tests

You can run all tests with one command:

`bun/run-tests`

## With coverage

This will do the same as above, but will also generate code coverage and dump you results

`bin/run-tests coverage`

## Submit to coveralls.io

This will do the same as the coverage command, but also submits the coverage file to coveralls.io (only travis-ci should do this)

`bin/run-tests coverage travis`

Note: if for some reason you want to run the above and submit to coveralls locally, you must first setup an environment var:

`export COVERALLS_RUN_LOCALLY=1`