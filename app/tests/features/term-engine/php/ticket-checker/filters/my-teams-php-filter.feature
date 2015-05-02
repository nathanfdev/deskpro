Feature: My Teams Filter
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Use the specific Term Engine data set
    Given I install the "term engine" data set
    And I am using the PhpTicketCheckerEngine

  Scenario: I check matching tickets
    Given I set the context agent to agent
    When I evaluate the filter "My Team's Tickets"
    And I check tickets with the following ids:
      | 10 |
      | 11 |
      | 12 |
      | 13 |
      | 14 |
      | 16 |
    Then all of the checks should match

  Scenario: I check matching tickets with a different agent
    Given I set the context agent to agent_chris
    When I evaluate the filter "My Team's Tickets"
    And I check tickets with the following ids:
      | 10 |
      | 11 |
      | 12 |
    Then all of the checks should match

  Scenario: I check non-matching tickets for the other agent
    Given I set the context agent to agent_chris
    When I evaluate the filter "My Team's Tickets"
    And I check tickets with the following ids:
      | 13 |
      | 14 |
      | 16 |
    Then none of the checks should match

  Scenario: I check non-matching tickets for agent
    Given I set the context agent to agent
    When I evaluate the filter "My Team's Tickets"
    And I check tickets with the following ids:
      | 20 |
      | 21 |
      | 4  |
    Then none of the checks should match