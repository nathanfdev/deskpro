@new
Feature: /ticket_forms endpoint
  I create a ticket with person w/o name

  Background:
    Given no Person records exist
    Given I'm authenticated as admin
    And the following User records exist:
      | #  | Email              |
      | u1 | user_1@deskpro.dev |
    And only the following Ticket records exist:
      | #  | Subject        |
      | t1 | Ticket subject |

  Scenario: I create a ticket by person id
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": ~u1~
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{u1}"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "User 1"

  Scenario: I create a ticket by person email
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": "user_1@deskpro.dev"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{u1}"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "User 1"
