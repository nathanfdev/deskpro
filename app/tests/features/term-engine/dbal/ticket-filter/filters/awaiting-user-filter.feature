Feature: Awaiting User Tickets Filter
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Use the specific Term Engine data set
    Given I install the "term engine" data set
    And I am using the DbalTicketFilterEngine

  Scenario: I run "Awaiting User" tickets filter and view a page
    Given I set the context agent to agent
    When I evaluate the filter "Awaiting User"
    And I run a count on the executable query
    Then I should be given the count of all tickets in the db with STATUS_AWAITING_USER
    And print the last run query
    When I fetch the ids from the executable query
    Then I should have an array with the following ids:
      | 21 |
      | 22 |
    And print the last run query