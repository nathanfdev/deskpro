Feature: /agent_teams/counts endpoint
  To retrieve number of agents in DeskPRO agent teams
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get counts of agents for all teams
    When I send a GET request to "/api/v2/agent_teams/counts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.nested.counts" should have 2 elements
