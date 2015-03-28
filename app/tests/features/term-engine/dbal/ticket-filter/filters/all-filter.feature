Feature: All Tickets Filter
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Basic database
    Given I install the basic data set

  Scenario: I run "All" tickets filter and view a page
    Given I set the context agent to agent
    And I set the context page to 3
    And I set the context count to 5
    When I evaluate the filter "All"
    And I run a count on the executable query
    Then I should be given the count of all tickets in the db
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