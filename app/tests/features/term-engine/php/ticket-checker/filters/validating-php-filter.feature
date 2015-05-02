Feature: Awaiting Validation Filter
  In order to easily filter an agents tickets
  As a developer
  I want to use the term engine with this filter

  Background: Use the specific Term Engine data set
    Given I install the "term engine" data set
    And I am using the PhpTicketCheckerEngine

  Scenario: I check matching tickets
    Given I set the context agent to agent
    When I evaluate the filter "Awaiting Validation"
    And I check tickets with the following ids:
      | 26 |
    Then all of the checks should match

  Scenario: I check non-matching tickets
    Given I set the context agent to agent
    When I evaluate the filter "Awaiting Validation"
    And I check tickets with the following ids:
      | 20 |
      | 21 |
      | 22 |
      | 23 |
      | 24 |
      | 25 |
      | 27 |
      | 1  |
      | 9  |
    Then none of the checks should match