@new
Feature: /ticket_forms
  I want to check person field

  Background:
    Given I have only default brand
    And no Person records exist
    And I'm authenticated as admin
    And "user_1@deskpro.dev" user exists
    And "user_2@deskpro.dev" user exists
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
    And only the following Ticket records exist:
      | #  | Brand          | Department | Subject  | Person               |
      | t1 | {defaultBrand} | {d1}       | Ticket 1 | {user_1@deskpro.dev} |
    And only the following TicketMessage records exist:
      | #  | Ticket | Person               | Message         |
      | m1 | {t1}   | {user_1@deskpro.dev} | my text message |

  Scenario Outline: I modify ticket person by id/email
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": <ref>
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.person" should be equal to "{user_2@deskpro.dev}"

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].person" should be equal to "{user_2@deskpro.dev}"

    Examples:
      | ref                  |
      | ~user_2@deskpro.dev~ |
      | "user_2@deskpro.dev" |

  Scenario: I modify ticket person by creating a new person using name and email fields
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": {
    "email": "new-user@deskpro.dev",
    "name": "Some NewUser"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.person" should exist

    When I send a GET request to "/api/v2/people?order_by=id&order_dir=desc"
    Then the response status code should be 200
    And the JSON node "data[0].name" should be equal to "Some NewUser"
    And the JSON node "data[0].primary_email" should be equal to "new-user@deskpro.dev"
    And the JSON node "data[0].emails[0]" should be equal to "new-user@deskpro.dev"

  Scenario: I modify ticket with a person by unknown email (inline)
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": "unknown-email@deskpro.dev"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.person" should exist

    When I send a GET request to "/api/v2/people?order_by=id&order_dir=desc"
    Then the response status code should be 200
    And the JSON node "data[0].name" should be equal to "Unknown-email"
    And the JSON node "data[0].primary_email" should be equal to "unknown-email@deskpro.dev"
    And the JSON node "data[0].emails[0]" should be equal to "unknown-email@deskpro.dev"

  Scenario: I modify ticket with a person by unknown email
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": {
    "email": "unknown-email@deskpro.dev"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.person" should exist

    When I send a GET request to "/api/v2/people?order_by=id&order_dir=desc"
    Then the response status code should be 200
    And the JSON node "data[0].name" should be equal to "Unknown-email"
    And the JSON node "data[0].primary_email" should be equal to "unknown-email@deskpro.dev"
    And the JSON node "data[0].emails[0]" should be equal to "unknown-email@deskpro.dev"

  Scenario: I edit specific person name via the ticket form
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": {
    "email": "user_1@deskpro.dev",
    "name": "Edited Name"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{user_1@deskpro.dev}"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Edited Name"

  Scenario: I edit own person name via the ticket form
    Given the "{t1}" record "person" prop is equal to "{admin}"
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": {
    "name": "My Edited Name"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "My Edited Name"

  Scenario: I check that person's props are not validated on assign to a ticket
    Given the following AgentTeam records exist:
      | #    | Name   |
      | team | Team 1 |
    And the following User records exist:
      | #  | Name     | Email               | Primary Team |
      | p1 | Person 1 | person1@example.com | {team}       |
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": ~p1~
}
    """
    Then the response status code should be 204
