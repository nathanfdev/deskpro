@new
Feature: /tickets/{ticket_id}/logs endpoint

  Background:
    Given I'm authenticated as admin
    And agent and user exist
    And only the following Ticket records exist:
      | #  | Subject  | Person |
      | t1 | Ticket 1 | {user} |
    And no TicketLog records exist

  Scenario: I get list of ticket logs
    Given only the following TicketLog records exist:
      | #  | Parent | Ticket | Person | Action Type    | Details                                            |
      | l1 |        | {t1}   | {user} | action_starter |                                                    |
      | l2 | {l1}   | {t1}   | {user} | changed_status | {"old_status": "", "new_status": "awaiting_agent"} |

    When I send a GET request to "/api/v2/tickets/{t1}/logs"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to "{l1}"
    And the JSON node "data[0].parent" should be null
    And the JSON node "data[0].person" should be equal to "{user}"
    And the JSON node "data[0].ticket" should be equal to "{t1}"
    And the JSON node "data[0].action_type" should be equal to "action_starter"
    And the JSON node "data[0].message_html" should contain "Updated by system"
    And the JSON node "data[0].message_text" should be equal to "Updated by system"
    And the JSON node "data[0].details" should exist
    And the JSON node "data[0].date_created" should exist

    And the JSON node "data[1].id" should be equal to "{l2}"
    And the JSON node "data[1].parent" should be equal to "{l1}"
    And the JSON node "data[1].person" should be equal to "{user}"
    And the JSON node "data[1].ticket" should be equal to "{t1}"
    And the JSON node "data[1].action_type" should be equal to "changed_status"
    And the JSON node "data[1].message_html" should contain "Status set"
    And the JSON node "data[1].message_html" should contain "<span class="
    And the JSON node "data[1].message_html" should contain "Awaiting Agent"
    And the JSON node "data[1].message_text" should be equal to "Status set to Awaiting Agent"
    And the JSON node "data[1].details.old_status" should be equal to the string ""
    And the JSON node "data[1].details.new_status" should be equal to "awaiting_agent"

  Scenario: I create a free ticket log with html message
    When I send a POST request to "/api/v2/tickets/{t1}/logs" with body:
    """
{
  "message_html": "<span>my custom message</span>"
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}/logs"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].action_type" should be equal to "free"
    And the JSON node "data[0].person" should be equal to "{admin}"
    And the JSON node "data[0].ticket" should be equal to "{t1}"
    And the JSON node "data[0].details.message_html" should be equal to "<span>my custom message</span>"

  Scenario: I create a free ticket log with text message
    When I send a POST request to "/api/v2/tickets/{t1}/logs" with body:
    """
{
  "message_text": "my custom message"
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}/logs"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].action_type" should be equal to "free"
    And the JSON node "data[0].details.message" should be equal to "my custom message"

  Scenario: I merge details with message
    When I send a POST request to "/api/v2/tickets/{t1}/logs" with body:
    """
{
  "message_text": "my custom message",
  "details": {
    "param_1": "value 1",
    "param_2": "value 2"
  }
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}/logs"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].action_type" should be equal to "free"
    And the JSON node "data[0].details.message" should be equal to "my custom message"
    And the JSON node "data[0].details.param_1" should be equal to "value 1"
    And the JSON node "data[0].details.param_2" should be equal to "value 2"

  Scenario: I try to send empty message
    When I send a POST request to "/api/v2/tickets/{t1}/logs"
    Then the response status code should be 400
    And the JSON node "errors.fields.message_html.errors[0].code" should be equal to "required"

  Scenario: I try to send details in wrong format
    When I send a POST request to "/api/v2/tickets/{t1}/logs" with body:
    """
{
  "message_text": "my custom message",
  "details": "my details"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.details.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I get list of ticket logs by ticket ref
    When I send a GET request to "/api/v2/tickets/ref:{t1:ref}/logs"
    Then the response status code should be 200
