Feature: All Tickets Filter (PhpTicketCheckerEngine)
  In order to easily check if a ticket matches this filter
  As a developer
  I want to use the php engine with this filter

  Background: Use the specific Term Engine data set
    Given I install the "term engine" data set
    And I am using the PhpTicketCheckerEngine

  Scenario: I check matching tickets
    Given I set the context agent to agent
    When I evaluate the filter "All"
    And I check tickets with the following ids:
      | 11 |
      | 12 |
      | 13 |
      | 14 |
      | 15 |
    Then all of the checks should match

  Scenario: I check non-matching tickets
    Given I set the context agent to agent
    When I evaluate the filter "All"
    And I check tickets with the following ids:
      | 22 |
      | 23 |
      | 24 |
      | 25 |
      | 26 |
      | 27 |
      | 28 |
    Then none of the checks should match

