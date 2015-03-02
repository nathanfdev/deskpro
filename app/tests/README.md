DeskPRO Test Suite
===================

# Getting Started

1. Copy "config.test.php.dist" to "config.test.php"
2. Open "config.test.php" and enter your db details (or just create the default deskpro_test database)

# Running Tests

We use 3 separate tools in the suite. Each can be run individually with their own commands:

1. PHPSpec
2. PHPUnit
3. Behat

## PHPSpec

PHPSpec (http://www.phpspec.net) is used to spec classes as you build them. It helps with OOP design and encourages
best practice via the S.O.L.I.D principles (http://en.wikipedia.org/wiki/SOLID_(object-oriented_design)). 

You can see the config file: `app/tests/phpspec.yml`

> please ignore `app/tests/phpspec.yml.cov` because it is just used to add coverage to the tests for CI

### Create a new spec (this will auto generate the files for you):

`bin/phpspec desc Application/SomeBundle/My/Class`

### Run the phpspec suite:

`bin/phpspec run`

### Run a specific spec

`bin/phpspec run spec/Application/SomeBundle/My/ClassSpec.php`

### Run a whole directory of specs

`bin/phpspec run spec/Application/SomeBundle`

## PHPUnit

Sometimes we want to use PHPUnit instead of PHPSpec. You can do this by creating phpunit test cases in `app/tests/phpunit`

You can see the config file: `app/tests/phpunit.xml`

> please ignore `app/tests/phpunit.xml.cov` because it is just used to add coverage to the tests for CI

### Run the phpunit suite:

`bin/phpunit`

## Behat

Behat is a BDD (behaviour driven design) tool that we use for both integration and functional testing, useing the
Gherkin language to write the tests.

You can see the config file: `app/tests/behat.yml`

> please ignore `app/tests/behat.yml.cov` because it is just used to add coverage to the tests for CI


Our behat suite is made up of two distinct **profiles**. You will notice that if you run `bin/behat`, you get an error:

This is because you need to specify which **profile** you want to run.

### Running the Portal Profile

`bin/behat --profile=portal`

### Running the API Profile

`bin/behat --profile=api`

> Our Travis-CI is configured to also use the `--config=behat.cov.yml` config file, which simply tells it to also create code coverage files. You won't need to worry about this during development, just use the defaul `behat.yml` config file.

### Run the behat suite:

`bin/behat`

### Run a specific feature file:

`bin/behat features/portal/languages.feature`

### Run a specific scenario on a feature file:

Assuming line 15 is the start of a Scenario: block:

`bin/behat features/portal/languages.feature:15`

### Run a folder of features:

`bin/behat features/portal/router`

## Behat Goodies

### database

In the tests/TestBundle we define some data sets and useful data related services. The DataSetContext does some
things for us:

If you say `Given I install the fresh data set`, then the `DpTests\TestBundle\DataSet\FreshDb` class is used to delete the database and reconstruct it.

However, many steps might want to ensure this is the data set they are using, and thus we would end up with the
same database being reinstalled over and over.

To prevent this, the DataSetContext will NOT reinstall the same dataset by default (it leaves the db as is).

Since the db is untouched, anything a previous scenario did to it will still be there, so if your scenario needs
a complete reinstall, just put the @reinstall tag above the scenario. (see tests.feature). Many scenarios don't
really care about arbitrary changes to the data, but some of your scenarios will, so keep this in mind.


### users and authentication

The fresh data set uses the values from a class named `DpTests\TestBundle\UserDetailsRepo` to create users. So, if you
installed the fresh data set you can use the "user_details" service to get information about the users in the static repo:

- admin
- agent
- user

For example to get the email of the "user" user, you can do:

`$this->getContainer()->get('user_details')->getEmail('user');`

This helps a lot when logging users in. In fact, we already have a context that handles that for you. See the
AuthContext for step definitions that log a user in.

`Given I am authenticated as admin`

`Then I should be authenticated as admin`

### Tips

1. If you inject services into your context, and then use the symfony2 mink driver to access a page, you need to
use the container in your context to get the service. The injected service will be an old/incorrect one, as the
driver actually uses a different container.

`$this->getContainer()->get('security.token_storage')` instead of `$this->token_storage` (assuming you injected it into your context)

# Run all tests

You can run all tests with one command:

`bun/run-tests`

## With coverage

This will do the same as above, but will also generate code coverage and dump you results

`bin/run-tests coverage`

## Submit to coveralls.io

This will do the same as the coverage command, but also submits the coverage file to coveralls.io (only travis-ci should do this)

`bin/run-tests coverage travis`

Note: if for some reason you want to run the coverage and submit to coveralls locally, you must first setup an environment var:

`export COVERALLS_RUN_LOCALLY=1`

and then run `bin/run-tests coverage travis`