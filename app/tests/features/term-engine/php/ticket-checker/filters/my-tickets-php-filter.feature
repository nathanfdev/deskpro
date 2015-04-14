Feature: My Tickets Filter
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Use the specific Term Engine data set
    Given I install the "term engine" data set
    And I am using the PhpTicketCheckerEngine

  Scenario: I check matching tickets
    Given I set the context agent to agent
    When I evaluate the filter "My Tickets"
    And I check tickets with the following ids:
      | 1  |
      | 2  |
      | 4  |
      | 6  |
      | 7  |
      | 8  |
      | 10 |
    Then all of the checks should match

  Scenario: I check non-matching tickets
    Given I set the context agent to agent
    When I evaluate the filter "My Tickets"
    And I check tickets with the following ids:
      | 3  |
      | 5  |
      | 9  |
      | 21 |
    Then none of the checks should match