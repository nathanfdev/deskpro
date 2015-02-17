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