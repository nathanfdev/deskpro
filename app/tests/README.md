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

## Behat Goodies

### database

In the tests/TestBundle we define some data sets and useful data related services. The DataSetContext does some
things for us:

If you say "Given I install the fresh data set", then the "FreshDbSet" class is used to delete the database and
reconstruct it.

However, many steps might want to ensure this is the data set they are using, and thus we would end up with the
same database being reinstalled over and over.

To prevent this, the DataSetContext will NOT reinstall the same dataset by default (it leaves the db as is).

Since the db is untouched, anything a previous scenario did to it will still be there, so if your scenario needs
a complete reinstall, just put the @reinstall tag above the scenario. (see tests.feature). Many scenarios don't
really care about arbitrary changes to the data, but some of your scenarios will, so keep this in mind.


### users and authentication

The fresh data set uses the values from a class named "DpBehat\TestBundle\UserDetailsRepo" to create users. So, if you
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

Note: if for some reason you want to run the above and submit to coveralls locally, you must first setup an environment var:

`export COVERALLS_RUN_LOCALLY=1`