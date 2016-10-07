@new
Feature: Default settings endpoint

  Background:
    Given I'm authenticated as "admin"
    And only the following "Brand" records exist:
      | #  | name    |
      | b1 | brand 1 |
      | b2 | brand 2 |
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
      | d2 | Department 2 | 1                  |
      | d3 | Department 3 | 1                  |
      | d4 | Department 4 | 1                  |
    And only the following brand settings records exist:
      | brand | name                     | value |
      | {b1}  | default_department.agent | {d1}  |
      | {b1}  | default_department.user  | {d2}  |
      | {b2}  | default_department.agent | {d3}  |
      | {b2}  | default_department.user  | {d4}  |

  Scenario: check settings resolve
    When I send a GET request to "/api/v2/settings/departments/default"
    Then the response should be in JSON

    And the response status code should be 200
    And the JSON node "data[0].brand" should be equal to "{b1}"
    And the JSON node "data[0].type" should be equal to "agent"
    And the JSON node "data[0].department" should be equal to "{d1}"
    And the JSON node "data[1].brand" should be equal to "{b1}"
    And the JSON node "data[1].type" should be equal to "user"
    And the JSON node "data[1].department" should be equal to "{d2}"
    And the JSON node "data[2].brand" should be equal to "{b2}"
    And the JSON node "data[2].type" should be equal to "agent"
    And the JSON node "data[2].department" should be equal to "{d3}"
    And the JSON node "data[3].brand" should be equal to "{b2}"
    And the JSON node "data[3].type" should be equal to "user"
    And the JSON node "data[3].department" should be equal to "{d4}"

  Scenario: check settings set
    When I send a PUT request to "/api/v2/settings/departments/default" with body:
    """
{
  "type": "user",
  "department": ~d2~,
  "brand": ~b1~
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/settings/departments/default"
    And the response status code should be 200
    And the JSON node "data[1].brand" should be equal to "{b1}"
    And the JSON node "data[1].type" should be equal to "user"
    And the JSON node "data[1].department" should be equal to "{d2}"

  Scenario: check settings remove
    When I send a PUT request to "/api/v2/settings/departments/default" with body:
    """
{
  "type": "agent",
  "department": null,
  "brand": ~b1~
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/settings/departments/default"
    And the response status code should be 200
    And the JSON node "data[0].brand" should be equal to "{b1}"
    And the JSON node "data[0].type" should be equal to "agent"
    And the JSON node "data[0].department" should be equal to "0"

  Scenario: check error on invalid input
    When I send a PUT request to "/api/v2/settings/departments/default" with body:
    """
{
  "type": "agent",
  "department": -20,
  "brand": ~b1~
}
    """
    Then the response status code should be 400
