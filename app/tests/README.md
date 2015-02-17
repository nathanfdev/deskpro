DeskPRO Test Suite
===================

# Getting Started

1. Copy "config.test.php.dist" to "config.test.php"
2. Open "config.test.php" and enter your db details (or just create the default deskpro_test database)

# Running Tests

We use 3 separate tools in the suite. Each can be run individually with their commands:

## PHPSpec

Run the phpspec suite:
bin/phpspec run

Create a new spec (this will auto generate the files for you):
bin/phpspec desc Application/SomeBundle/My/Class

## PHPUnit

Run the phpunit suite:
bin/phpunit

## Behat

Run the behat suite:
bin/behat

# Run all tests

You can run all tests with one command:

bun/run-tests