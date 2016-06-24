@new
Feature: /ticket_forms endpoint
  I want to check fields visibility

  Background:
    Given I'm authenticated as admin
    And a user with "user_1@deskpro.dev" email exists
    And a user with "user_2@deskpro.dev" email exists
    And only the following Department records exist:
      | #  | Parent | Title        | Is Tickets Enabled |
      | d1 |        | Department 1 | 1                  |
      | d2 |        | Department 2 | 1                  |

  Scenario: I check that all fields present on the form on create a new ticket
    Given the only default ticket layout exists with fields:
      | agent_layout |
      | cc           |
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~user_1@deskpro.dev~,
  "department": ~d1~,
  "message": {
    "message": "my message"
  },
  "cc": ["user_2@deskpro.dev"]
}
    """
    Then the response status code should be 201

  Scenario: I check hidden fields on create a new ticket
    Given the only default ticket layout exists with fields:
      | agent_layout | agent_layout_options    |
      | cc           | {"on_newticket": false} |
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "cc": ["user_2@deskpro.dev"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: cc"

  Scenario: I check that all fields present on the form on edit ticket
    Given the only default ticket layout exists with fields:
      | agent_layout |
      | cc           |
    And only the following Ticket records exist:
      | #        | Person               | Subject        |
      | ticket_1 | {user_1@deskpro.dev} | Ticket Subject |
    When I send a PUT request to "/api/v2/ticket_forms/agent/{ticket_1}" with body:
    """
{
  "cc": ["user_2@deskpro.dev"]
}
    """
    Then the response status code should be 204

  Scenario: I check hidden fields on edit ticket
    Given the only default ticket layout exists with fields:
      | agent_layout | agent_layout_options     |
      | cc           | {"on_editticket": false} |
    And only the following Ticket records exist:
      | #        | Person               | Subject        |
      | ticket_1 | {user_1@deskpro.dev} | Ticket Subject |
    When I send a PUT request to "/api/v2/ticket_forms/agent/{ticket_1}" with body:
    """
{
  "cc": ["user_2@deskpro.dev"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: cc"

  Scenario: I check hidden field on change layout
    Given the only default ticket layout exists with fields:
      | agent_layout | agent_layout_options   |
      | cc           | {"on_editticket": true} |
    And the ticket layout exists for "d2" department with fields:
      | agent_layout | agent_layout_options    |
      | cc           | {"on_editticket": false} |
    And only the following Ticket records exist:
      | #        | Person               | Department | Subject        |
      | ticket_1 | {user_1@deskpro.dev} | {d1}       | Ticket Subject |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{ticket_1}" with body:
    """
{
  "cc": ["user_2@deskpro.dev"]
}
    """
    Then the response status code should be 204

    When I send a PUT request to "/api/v2/ticket_forms/agent/{ticket_1}" with body:
    """
{
  "department": ~d2~,
  "cc": ["user_2@deskpro.dev"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: cc"
