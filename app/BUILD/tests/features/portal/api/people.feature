Feature: Widget Chat

  Background: Fresh database
    Given I install the fresh data set

  Scenario: I get online agents
    When I send a GET request to "/portal/api/people/online_agents"
    Then the response status code should be 200
    And the response should be in JSON
