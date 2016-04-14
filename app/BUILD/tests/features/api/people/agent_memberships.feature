@people
Feature: /people endpoint
  I want to check setting agent teams

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I try to set agent teams to user
    When I send a PUT request to "/api/v2/people/3" with body:
    """
{
  "primary_team": 1,
  "teams": [1, 2]
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.primary_team.errors[0].code" should be equal to "empty"
    And the JSON node "errors.fields.primary_team.errors[0].message" should be equal to "This value should be blank."
    And the JSON node "errors.fields.teams.errors[0].code" should be equal to "too_many_elements"
    And the JSON node "errors.fields.teams.errors[0].message" should be equal to "This collection should contain 0 elements or less."

  Scenario: I set agent teams to agent
    When I send a PUT request to "/api/v2/people/2" with body:
    """
{
  "primary_team": 2,
  "teams": [1, 2]
}
    """
    Then the response status code should be 204

    And I send a GET request to "/api/v2/people/2"
    And the JSON node "data.primary_team" should be equal to 2
    And the JSON node "data.teams" should have 2 elements
    And the JSON node "data.teams[0]" should be equal to 1
    And the JSON node "data.teams[1]" should be equal to 2
