@new
Feature: Unset agent props on change an agent to end-user

  Background:
    Given no Person records exist
    And I'm authenticated as "admin"
    And the following AgentTeam records exist:
      | #  | Name   |
      | t1 | Team 1 |
      | t2 | Team 2 |
      | t3 | Team 3 |
    And only the following AgentData records exist:
      | #  | Extension Number |
      | a1 | 1001             |
    And the following Agent records exist:
      | #  | Name     | Email               | Primary Team | Teams              | Agent Data |
      | p1 | Person 1 | person1@example.com | {t1}         | [{t1}, {t2}, {t3}] | {a1}       |

  Scenario: I check all agent props are unset
    When I send a PUT request to "/api/v2/people/{p1}" with body:
    """
{
  "is_agent": false
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/{p1}"
    Then the JSON node "data.is_agent" should be equal to 0
    And the JSON node "data.primary_team" should be equal to 0
    And the JSON node "data.teams" should have 0 elements
    And the JSON node "data.agent_data" should be null

  Scenario: I check all agent props are still set
    When I send a PUT request to "/api/v2/people/{p1}" with body:
    """
{
  "is_agent": true
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/{p1}"
    Then the JSON node "data.is_agent" should be equal to 1
    And the JSON node "data.primary_team" should be equal to "{t1}"
    And the JSON node "data.teams" should have 3 elements
    And the JSON node "data.agent_data.extension_number" should be equal to "1001"
