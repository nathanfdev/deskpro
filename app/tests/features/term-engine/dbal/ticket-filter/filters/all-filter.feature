Feature: All Tickets Filter
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Use the specific Term Engine data set
    Given I install the "term engine" data set

  Scenario: I run "All" tickets filter and view a page
    Given I set the context agent to agent
    When I evaluate the filter "All"
    And I set the executable query page to 3
    And I set the executable query count to 5
    And I run a count on the executable query
    Then I should be given the count of all tickets in the db with STATUS_AWAITING_AGENT
    And print the last run query
    When I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 11 |
      | 12 |
      | 13 |
      | 14 |
      | 15 |
    And print the last run query

  Scenario: I run "All" tickets filter AND I append a custom WHERE clause
    Given I set the context agent to agent
    When I evaluate the filter "All"
    And I append department = 2 to the executable query andGroupWhere
    And I run a count on the executable query
    And print the last run query
    Then I should be given the count 2
    When I fetch the ids from the executable query
    Then I should be given the following dbal rows:
      | id |
      | 6  |
      | 7  |
    And print the last run query