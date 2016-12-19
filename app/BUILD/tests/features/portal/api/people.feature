@new
Feature: Widget Chat

  Scenario: I get online agents
    When I send a GET request to "/portal/api/people/online_agents"
    Then the response status code should be 200
    And the response should be in JSON
