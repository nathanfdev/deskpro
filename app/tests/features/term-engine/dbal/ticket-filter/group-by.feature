Feature: Group By
  In order to see ticket counts using various WHERE and GROUP BY settings
  As a developer
  I need the DbalTicketFilterEngine to process my GROUP BY commands

  Background: Use the specific Term Engine data set
    Given I install the "term engine" data set
    And I am using the DbalTicketFilterEngine

  Scenario: I group tickets by agent (simple grouping)
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

  Scenario: I group tickets by agent AND department (multiple groupings)
    Given I set the context agent to agent
    When I evaluate the filter "All"
    And I add the count group "agent" to the executable query
    And I add the count group "department" to the executable query
    And I fetch the grouped count from the executable query
    And print the last run query
    Then I should be given the following dbal rows:
      | count | agent | department |
      | 5     | null  | 1          |
      | 3     | 1     | 1          |
      | 5     | 2     | 1          |
      | 2     | 2     | 2          |
      | 5     | 3     | 1          |

  Scenario: I group tickets by department with a group WHERE (select agent id = 2, group by department)
    Given I set the context agent to agent
    When I evaluate the filter "All"
    And I add the count group "department" to the executable query
    And I append agent = 2 to the executable query andGroupWhere
    And I fetch the grouped count from the executable query
    And print the last run query
    Then I should be given the following dbal rows:
      | count | department |
      | 5     | 1          |
      | 2     | 2          |
