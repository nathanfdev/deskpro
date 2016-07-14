@new
Feature: /ticket_forms validation
  I want to check form errors sideloading validation

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | # | Subject |
      | t |         |

  Scenario: I check sideloading of form errors
    When I send a GET request to "/api/v2/tickets/{t}?include=ticket_agent_errors,ticket_user_errors"
    Then the response status code should be 200

    And the JSON node "linked.ticket_agent_errors.{t}.fields.subject.errors[0].code" should be equal to "required"
    And the JSON node "linked.ticket_user_errors.{t}.fields.subject.errors[0].code" should be equal to "required"

  Scenario: I check sideloading of form errors
    When I send a POST request to "/api/v2/tickets/{t}/messages?with_ticket_validation=1" with body:
    """
{
  "message": "my message"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.ticket.fields.subject.errors[0].code" should be equal to "required"
