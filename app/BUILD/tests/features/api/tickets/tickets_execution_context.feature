@new
Feature: /tickets endpoint
  To check tickets API execution context
  As an API user
  I want an API endpoint

  Background:
    Given no Person records exist
    And no Ticket records exist
    And I reset ticket logs
    And I'm authenticated as admin
    And agent and user exist

  Scenario: I create a ticket as User
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Test Ticket",
  "person":  ~user~,
  "agent": ~agent~
}
    """
    Then the response status code should be 201
    And the "{lastCreatedId}" ticket should have "ticket_created" log with detail "event_performer" = "user"

  Scenario: I create a ticket as Agent
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Test Ticket",
  "person":  ~agent~,
  "agent": ~agent~
}
    """
    Then the response status code should be 201
    And the "{lastCreatedId}" ticket should have "ticket_created" log with detail "event_performer" = "agent"

  Scenario: I modify ticket as Agent
    Given only the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |
    When I reset ticket logs
    And I send a PUT request to "/api/v2/tickets/{t1}" with body:
    """
{
  "subject": "Modified 4"
}
    """
    And the response status code should be 204
    And the "{t1}" ticket should have "action_starter" log with detail "event" = "update"
    And the "{t1}" ticket should have "action_starter" log with detail "event_performer" = "agent"

  Scenario: I modify ticket as User
    Given only the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |
    When I reset ticket logs
    And I send a PUT request to "/api/v2/tickets/{t1}" with body:
    """
{
  "subject": "Modified 4",
  "person":  ~user~
}
    """
    And the response status code should be 204
    And the "{t1}" ticket should have "action_starter" log with detail "event" = "update"
    And the "{t1}" ticket should have "action_starter" log with detail "event_performer" = "agent"

  Scenario: I add ticket message as Agent
    Given only the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |
    When I reset ticket logs
    And I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "Agent message"
}
    """
    Then the response status code should be 201
    And the JSON node "data.person" should be equal to "{admin}"
    And the "{t1}" ticket should have "action_starter" log with detail "event" = "newreply"
    And the "{t1}" ticket should have "action_starter" log with detail "event_performer" = "agent"

  Scenario: I add ticket message as User
    Given only the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |
    When I reset ticket logs
    And I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "Agent message",
  "person": ~user~
}
    """
    Then the response status code should be 201
    And the JSON node "data.person" should be equal to "{user}"
    And the "{t1}" ticket should have "action_starter" log with detail "event" = "newreply"
    And the "{t1}" ticket should have "action_starter" log with detail "event_performer" = "user"
