Feature: My Tickets Filter
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Basic database
    Given I install the basic data set

  Scenario: I use the built in "My Tickets" filter with user "agent"
    Given I set the context agent to agent
    When I evaluate the filter "My Tickets"
    And I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 1  |
      | 2  |
      | 4  |
      | 6  |
      | 7  |
      | 8  |
      | 10 |
    And print the last run query

  Scenario: I use the built in "My Tickets" filter with user "admin"
    Given I set the context agent to admin
    When I evaluate the filter "My Tickets"
    And I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 3  |
      | 5  |
      | 9  |
    And print the last run query

  Scenario: I use a descending ordering
    Given I set the context agent to agent
    And I add the context order to id DESC
    When I evaluate the filter "My Tickets"
    And I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 10 |
      | 8  |
      | 7  |
      | 6  |
      | 4  |
      | 2  |
      | 1  |
    And print the last run query

  Scenario: I paginate an agent's tickets to see the first and third page
    Given I set the context agent to agent
    And I add the context order to id DESC
    And I set the context page to 1
    And I set the context count to 2
    When I evaluate the filter "My Tickets"
    And I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 10 |
      | 8  |
    And print the last run query
    When I set the context page to 3
    When I evaluate the filter "My Tickets"
    And I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 4  |
      | 2  |
    And print the last run query