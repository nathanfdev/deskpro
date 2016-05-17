Feature: tests
  In order to know the code I write works
  As a developer
  I need to be able to run the test suite

  Scenario: I run the suite
    Given I execute the test suite
    When this scenario is called
    Then this scenario should be successful

  Scenario: I inject a service into the context from the container
    Given I injected session into the context
    When I execute the test suite
    Then I should see that I have the session

  Scenario: I make use of a database fixture
    Given I have access to dataset_manager
    When I install the fresh data set
    Then I should be able to load an admin user

  Scenario: Data sets are NOT reinstalled if already installed
    Given I have already installed the fresh data set
    And I have not tagged this scenario with "reinstall"
    When I attempt to reinstall the fresh data set
    Then the database will not be installed

  Scenario: Data sets are reinstalled (wiped db with a clean install) if its tagged
    Given I have already installed the fresh data set
    And I have tagged this scenario with "reinstall"
    When I attempt to reinstall the fresh data set
    Then the database will be reinstalled

  Scenario: If its a different data set to be installed, then the tags don't matter
    Given I have already installed the fresh data set
    And I have not tagged this scenario with "reinstall"
    When I install the empty data set
    Then the database will be installed
