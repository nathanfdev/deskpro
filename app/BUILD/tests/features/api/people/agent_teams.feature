@crm-nav @tasks-nav
Feature: /agent_teams endpoint
  To retrieve DeskPRO agent teams
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @basic
  Scenario: I get list of all agent teams
    When I send a GET request to "/api/v2/agent_teams"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].name" should be equal to "test team"

  Scenario: I get MY teams
    When I send a GET request to "/api/v2/agent_teams?my=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data" should have 1 element

  Scenario: I get a single agent team
    When I send a GET request to "/api/v2/agent_teams/2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to "2"
    And the JSON node "data.name" should be equal to "Support Managers"

  Scenario: I get agents list from specific team
    When I send a GET request to "/api/v2/agent_teams/1/agents"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data[0]" should exist
    And the JSON node "data[0].id" should exist

  Scenario: I create agent team
    When I send a POST request to "/api/v2/agent_teams" with body:
    """
{
  "name": "Just created team",
  "members": [1,2]
}
"""
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.name" should be equal to "Just created team"

  Scenario: I'm trying to create a team with person included
    When I send a POST request to "/api/v2/agent_teams" with body:
    """
{
  "name": "Wrong team",
  "members": [1, 2, 3]
}
"""
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.members.errors" should exist
    And the JSON node "errors.fields.members.errors[0]" should exist
    And the JSON node "errors.fields.members.errors[0].code" should be equal to "person_not_agent"

  Scenario: I change the team
    When I send a PUT request to "/api/v2/agent_teams/1" with body:
    """
{
  "name": "Just updated team",
  "members": [1,2]
}
"""
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/api/v2/agent_teams/1/agents"
    Then the response should be in JSON
    And the JSON node "data" should have 2 elements

    When I send a PUT request to "/api/v2/agent_teams/1" with body:
    """
{
  "name": "Just updated team",
  "members": [1]
}
"""
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/api/v2/agent_teams/1/agents"
    Then the response should be in JSON
    And the JSON node "data" should have 1 element
