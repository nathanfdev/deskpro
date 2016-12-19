@new
Feature: /people endpoint
  I want to check setting agent teams

  Background:
    Given I'm authenticated as "admin"
    And only the following "AgentTeam" records exist:
      | #  | name   |
      | t1 | Team 1 |
      | t2 | Team 2 |

  Scenario: I try to set agent teams to user
    Given "user@deskpro.dev" user exists
    When I send a PUT request to "/api/v2/people/{user}" with body:
    """
{
  "primary_team": ~t1~,
  "teams": [~t1~, ~t2~]
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.primary_team.errors[0].code" should be equal to "empty"
    And the JSON node "errors.fields.primary_team.errors[0].message" should be equal to "This value should be blank."
    And the JSON node "errors.fields.teams.errors[0].code" should be equal to "too_many_elements"
    And the JSON node "errors.fields.teams.errors[0].message" should be equal to "This collection should contain 0 elements or less."

  Scenario: I set agent teams to agent
    Given "agent@deskpro.dev" agent exists
    When I send a PUT request to "/api/v2/people/{agent}" with body:
    """
{
  "primary_team": ~t2~,
  "teams": [~t1~, ~t2~]
}
    """
    Then the response status code should be 204

    And I send a GET request to "/api/v2/people/{agent}"
    And the JSON node "data.primary_team" should be equal to "{t2}"
    And the JSON node "data.teams" should have 2 elements
    And the JSON node "data.teams[0]" should be equal to "{t1}"
    And the JSON node "data.teams[1]" should be equal to "{t2}"
