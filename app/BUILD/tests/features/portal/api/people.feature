@new
Feature: Widget Chat

  Background:
    Given I have guest portal api session with code "AAAAAAAAAAAAAAA"

  Scenario: I get online agents
    When I send a GET request to "/portal/api/people/online_agents?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200
    And the response should be in JSON
