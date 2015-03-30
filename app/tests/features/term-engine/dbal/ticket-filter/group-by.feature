Feature: Group By
  In order to see ticket counts using various WHERE and GROUP BY settings
  As a developer
  I need the DbalTicketFilterEngine to process my GROUP BY commands

  Background: Basic database
    Given I install the basic data set

  Scenario: I group tickets by agent
    Given I set the context agent to agent
    When I evaluate the filter "All"
    And I add the count group "agent" to the executable query
    And I fetch the grouped count from the executable query
    And print the last run query
    Then I should be given the following dbal rows:
      | count | agent |
      | 5     | null  |
      | 3     | 1     |
      | 7     | 2     |
      | 5     | 3     |
      | 20    | null  |