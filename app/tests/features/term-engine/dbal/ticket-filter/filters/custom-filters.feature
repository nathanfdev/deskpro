Feature: Custom Filters
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Use the specific Term Engine data set
    Given I install the "term engine" data set
    And I am using the DbalTicketFilterEngine

  Scenario: I construct a custom filter and evaluate it
    Given I set the context agent to agent
    When I evaluate the filter "Fav. Color is Red"
    And I run a count on the executable query
    Then I should be given the count 1
    And print the last run query
    When I fetch the ids from the executable query
    Then I should have an array with the following ids:
      | 7 |
    And print the last run query

  Scenario: I construct a custom filter and evaluate it
    Given I set the context agent to agent
    When I evaluate the filter "Fav. Color is Red or Blue"
    And I append id ASC to the executable query order
    And I run a count on the executable query
    Then I should be given the count 3
    And print the last run query
    When I fetch the ids from the executable query
    Then I should have an array with the following ids:
      | 2 |
      | 4 |
      | 7 |
    And print the last run query