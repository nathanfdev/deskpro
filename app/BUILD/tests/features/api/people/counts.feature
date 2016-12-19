@new
Feature: /people/counts endpoint
  To retrieve counts of DeskPRO people
  As an API user
  I want an API endpoint

  Background:
    Given there are no "Person" records
    And I'm authenticated as "admin"

  Scenario: I count agents filtering out soft-deleted ones, grouped by teams
    Given "agent1@deskpro.dev" agent exists
    And "agent2@deskpro.dev" agent exists
    And "agent3@deskpro.dev" agent exists
    And "user1@deskpro.dev" user exists
    And "user2@deskpro.dev" user exists
    And only the following "AgentTeam" records exist:
      | #  | name  | members |
      | t1 | Team1 | [{me}]  |
      | t2 | Team2 | []      |
    And I send a DELETE request to "/api/v2/agents/{agent3@deskpro.dev}"

    When I send a GET request to "/api/v2/people/counts?is_agent=1&is_deleted=0&group_by=agent_team"
    
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.nested" should exist
    And the JSON node "data.nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].id" should be equal to "0"
    And the JSON node "data.nested[0].type" should be equal to "agent_team"
    And the JSON node "data.nested[1].title" should be equal to "Team1"
    And the JSON node "data.nested[1].count" should be equal to 1
    And the JSON node "data.nested[1].id" should be equal to "{t1}"
    And the JSON node "data.nested[1].type" should be equal to "agent_team"

  Scenario: I count users filtering out soft-deleted ones, grouped by user groups
    Given "agent1@deskpro.dev" agent exists
    And "agent2@deskpro.dev" agent exists
    And "agent3@deskpro.dev" agent exists
    And "user1@deskpro.dev" user exists
    And "user2@deskpro.dev" user exists
    And I send a DELETE request to "/api/v2/agents/{agent3@deskpro.dev}"
    And "group" user group exists
    And I add "user1@deskpro.dev" usergroup relation "group"
    And I add "user2@deskpro.dev" usergroup relation "group"

    When I send a GET request to "/api/v2/people/counts?is_agent=0&is_deleted=0&group_by=user_group"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 2
    And the JSON node "data.nested" should exist
    And the JSON node "data.nested[0].title" should be equal to "group"
    And the JSON node "data.nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].id" should be equal to "{group_group}"
    And the JSON node "data.nested[0].type" should be equal to "user_group"

  Scenario: I count soft-deleted people
    Given "agent1@deskpro.dev" agent exists
    And I send a DELETE request to "/api/v2/agents/{agent1@deskpro.dev}"
    When I send a GET request to "/api/v2/people/counts?is_deleted=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 1
