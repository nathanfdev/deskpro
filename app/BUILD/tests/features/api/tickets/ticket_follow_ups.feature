@new
Feature: /ticket_follow_ups endpoint

  Background:
    Given I'm authenticated via session as admin
    And the setting "beta_features.follow_up" is set to 1
    And agent and user exist
    And only the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |
      | t2 | Ticket 2 | awaiting_agent |
    And only the following TicketFollowUp records exist:
      | #  | Ticket | Person  | Status  | Date To Run         | Cancel If User Reply | Actions                                          |
      | f1 | {t1}   | {agent} | pending | 2017-11-05 00:00:00 | 1                    | {"type": "agent", "options": {"agent": ~admin~}} |
      | f2 | {t1}   | {admin} | pending | 2017-11-08 00:00:00 | 0                    | {"type": "agent", "options": {"agent": ~admin~}} |
      | f3 | {t2}   | {admin} | pending | 2017-11-08 00:00:00 | 0                    | {"type": "agent", "options": {"agent": ~admin~}} |

  Scenario: I create a ticket follow up
    When I send a POST request to "/api/v2/tickets/{t1}/follow-ups" with body:
    """
{
  "date_to_run": "2017-11-09",
  "cancel_if_user_reply": true,
  "actions": [
    {
      "type": "agent",
      "options": {"agent": ~admin~}
    }
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.cancel_if_user_reply" should be equal to 1
    And the JSON node "data.status" should be equal to the string "pending"
    And the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.actions[0].type" should be equal to the string "agent"
    And the JSON node "data.actions[0].options.agent" should be equal to "{admin}"
    And the JSON node "data.date_to_run" should be equal to the string "2017-11-09T00:00:00+0000"

  Scenario: I retrieve a list of ticket follow ups
    When I send a GET request to "/api/v2/tickets/{t1}/follow-ups?order_by=id&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{f1}"
    And the JSON node "data[1].id" should be equal to "{f2}"

  Scenario: I retrieve a list of ticket follow ups by ticket ref
    When I send a GET request to "/api/v2/tickets/{t1:ref}/follow-ups?order_by=id&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{f1}"
    And the JSON node "data[1].id" should be equal to "{f2}"

  Scenario: I get a follow up
    When I send a GET request to "/api/v2/tickets/{t2}/follow-ups/{f3}"
    Then the response status code should be 200

  Scenario: I try to get a follow up from another ticket
    When I send a GET request to "/api/v2/tickets/{t1}/follow-ups/{f3}"
    Then the response status code should be 404

  Scenario: I try to cancel a follow up from another ticket
    When I send a GET request to "/api/v2/tickets/{t1}/follow-ups/{f3}"
    Then the response status code should be 404

  Scenario: I cancel a follow up
    When I send a POST request to "/api/v2/tickets/{t2}/follow-ups/{f3}/cancel"
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t2}/follow-ups/{f3}"
    Then the JSON node "data.status" should be equal to the string "cancelled"
