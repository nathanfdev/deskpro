Feature: Unassigned Tickets Filter
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Basic database
    Given I install the basic data set

  Scenario: I use the built in "Unassigned" filter with user "agent"
    Given I set the context agent to agent
    When I evaluate the filter "Unassigned"
    And I run a count on the executable query
    And print the last run query
    Then I should be given the count 4
    And print the last run query
    When I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 17 |
      | 18 |
      | 19 |
      | 20 |
    And print the last run query

  Scenario: I use a descending ordering
    Given I set the context agent to agent
    And I add the context order to id DESC
    When I evaluate the filter "Unassigned"
    And I run a count on the executable query
    Then I should be given the count 4
    And print the last run query
    When I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 20 |
      | 19 |
      | 18 |
      | 17 |
    And print the last run query

  Scenario: I paginate an agent's tickets to see the second page
    Given I set the context agent to agent
    And I add the context order to id DESC
    And I set the context page to 3
    And I set the context count to 1
    When I evaluate the filter "Unassigned"
    And I run a count on the executable query
    Then I should be given the count 4
    And print the last run query
    When I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 18 |
    And print the last run query