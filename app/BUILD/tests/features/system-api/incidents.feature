Feature: /system/incidents endpoint
  To manage DeskPRO incidents
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And there are no incidents
    And I add a continuing incident #1
    And I add a resolved incident #2
    And my request is authenticated

  Scenario: I retrieve an incident
    When I send a request to retrieve the incident #1
    Then the response status code should be 200
    And the JSON node "data.instructions_html" should exist
    And the JSON node "data.incident.title" should exist

  Scenario: I retrieve list of incidents
    When I send a GET request to "/api/v2/system/incidents"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

  Scenario: I dismiss an incident
    When I send a request to modify the incident #1 with the following data:
    """
{
  "dismissed": true
}
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I modify and retrieve an incident
    Given I send a request to modify the incident #1 with the following data:
    """
{
  "dismissed": true
}
    """
    When I send a request to retrieve the incident #1
    Then the response status code should be 200
    And the JSON node "data.incident.dismissed" should be equal to true

  Scenario: I delete an incident
    When I send a request to delete the incident #1
    Then the response should be in JSON
    And the response status code should be 200
