@basic
Feature: API on fresh data set

  Scenario: Accessing /people endpoint
    Given I install the fresh data set
    And I set permission "agent_people.use" = 1 for "registered" usergroup
    And my request is authenticated

    When I send a GET request to "/api/v2/people"
    Then the response status code should be 200
    And the JSON node "data" should exist
