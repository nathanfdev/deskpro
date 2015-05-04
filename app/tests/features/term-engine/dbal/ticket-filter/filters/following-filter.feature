Feature: Following Tickets Filter
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Use the specific Term Engine data set
    Given I install the "term engine" data set
    And I am using the DbalTicketFilterEngine

  Scenario: I use the built in "Tickets I Follow" filter with user "agent"
    Given I set the context agent to agent
    When I evaluate the filter "Tickets I Follow"
    And I run a count on the executable query
    Then I should be given the count 4
    And print the last run query
    When I fetch the ids from the executable query
    Then I should have an array with the following ids:
      | 9  |
      | 11 |
      | 12 |
      | 13 |
    And print the last run query

  Scenario: I use the built in "Tickets I Follow" filter with user "agent" reverse order
    Given I set the context agent to agent
    When I evaluate the filter "Tickets I Follow"
    And I set the executable query count to 2
    And I append ticket.id DESC to the executable query order
    And I run a count on the executable query
    And print the last run query
    Then I should be given the count 4
    When I fetch the ids from the executable query
    And print the last run query
    Then I should have an array with the following ids:
      | 13 |
      | 12 |