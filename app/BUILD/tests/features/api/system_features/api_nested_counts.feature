@new
Feature: CRUD nested counts

  Background:
    Given there are no "Person" records
    And I'm authenticated as agent
    And only the following AgentTeam records exist:
      | #  | Name   |
      | t1 | Team 1 |
      | t2 | Team 2 |
      | t3 | Team 3 |
    And the following Agent records exist:
      | #  | Name    | Primary Team |
      | a1 | Agent 1 | NULL         |
      | a2 | Agent 2 | {t1}         |
      | a3 | Agent 3 | {t2}         |
      | a4 | Agent 4 | {t2}         |
      | a5 | Agent 5 | {t3}         |

  Scenario: I select count w/o group by
    When I send a GET request to "/api/v2/people/counts"
    Then the JSON node "data.count" should be equal to 6
    And the JSON node "data.grouped_by" should be null
    And the JSON node "data.nested" should have 0 elements

  Scenario: I select count w/ array of nested elements
    When I send a GET request to "/api/v2/people/counts?group_by=agent_team"
    Then the JSON node "data.count" should be equal to 6
    And the JSON node "data.grouped_by" should be equal to "agent_team"
    And the JSON node "data.nested" should have 4 elements
    And the JSON node "data.nested[0].id" should be equal to 0
    And the JSON node "data.nested[1].id" should be equal to "{t1}"
    And the JSON node "data.nested[2].id" should be equal to "{t2}"
    And the JSON node "data.nested[3].id" should be equal to "{t3}"

  Scenario: I select count w/ map of nested elements
    When I send a GET request to "/api/v2/people/counts?group_by=agent_team&index_group_by=1"
    Then the JSON node "data.count" should be equal to 6
    And the JSON node "data.grouped_by" should be equal to "agent_team"
    And the JSON node "data.nested" should have 4 elements
    And the JSON node "data.nested.{t1}.id" should be equal to "{t1}"
    And the JSON node "data.nested.{t2}.id" should be equal to "{t2}"
    And the JSON node "data.nested.{t3}.id" should be equal to "{t3}"
