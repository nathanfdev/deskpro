@crm-nav @tasks-nav
Feature: /agent_teams endpoint
  To retrieve DeskPRO agent teams
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get list of all agent teams
    When I send a GET request to "/api/v2/agent_teams"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].name" should be equal to "test team"

  Scenario: I get a single agent team
    When I send a GET request to "/api/v2/agent_teams/2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to "2"
    And the JSON node "data.name" should be equal to "Support Managers"

  Scenario: I get a single agent team
    When I send a GET request to "/api/v2/agent_teams/2/agents"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data[0]" should exist
    And the JSON node "data[0].id" should exist