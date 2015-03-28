Feature: My Team's Tickets Filter
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Basic database
    Given I install the basic data set

  Scenario: I use the built in "My Team's Tickets" filter with user "agent"
    Given I set the context agent to agent
    When I evaluate the filter "My Team's Tickets"
    And I run a count on the executable query
    And print the last run query
    Then I should be given the count 5
    And print the last run query
    When I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 10 |
      | 11 |
      | 12 |
      | 13 |
      | 14 |
    And print the last run query

  Scenario: I use the built in "My Tickets" filter with user "agent_chris"
    Given I set the context agent to agent_chris
    When I evaluate the filter "My Team's Tickets"
    And I run a count on the executable query
    Then I should be given the count 3
    And print the last run query
    When I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 10 |
      | 11 |
      | 12 |
    And print the last run query

  Scenario: I use the built in "My Tickets" filter with user "admin"
    Given I set the context agent to admin
    When I evaluate the filter "My Team's Tickets"
    And I run a count on the executable query
    Then I should be given the count 0

  Scenario: I use a descending ordering
    Given I set the context agent to agent
    And I add the context order to id DESC
    When I evaluate the filter "My Team's Tickets"
    And I run a count on the executable query
    Then I should be given the count 5
    And print the last run query
    When I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 14 |
      | 13 |
      | 12 |
      | 11 |
      | 10 |
    And print the last run query

  Scenario: I paginate an agent's tickets to see the first page
    Given I set the context agent to agent
    And I add the context order to id DESC
    And I set the context page to 1
    And I set the context count to 3
    When I evaluate the filter "My Team's Tickets"
    And I run a count on the executable query
    Then I should be given the count 5
    And print the last run query
    When I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 14 |
      | 13 |
      | 12 |
    And print the last run query