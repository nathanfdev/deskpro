Feature: Resolved Filter
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Basic database
    Given I install the basic data set

  Scenario: I run the filter
    Given I set the context agent to agent
    When I evaluate the filter "Resolved"
    And I run a count on the executable query
    Then I should be given the count 2
    And print the last run query
    When I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 23 |
      | 24 |
    And print the last run query