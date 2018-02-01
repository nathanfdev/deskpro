@new
Feature: /tickets endpoint
  To check tickets API execution context
  As an API user
  I want an API endpoint

  Background:
    Given no Person records exist
    And I'm authenticated as admin
    And agent and user exist
    And I have a Department record referenced as department

  Scenario: I create a ticket for User
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Test Ticket",
  "department": ~department~,
  "person":  ~user~,
  "agent": ~agent~
}
    """
    Then the response status code should be 201
    And the "{lastCreatedId}" ticket should have "ticket_created" log with detail "event_performer" = "user"

  Scenario: I create a ticket for Agent
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Test Ticket",
  "department": ~department~,
  "person":  ~agent~,
  "agent": ~agent~
}
    """
    Then the response status code should be 201
    And the "{lastCreatedId}" ticket should have "ticket_created" log with detail "event_performer" = "agent"

  Scenario: I modify ticket as Agent
    Given I have a Ticket record referenced as ticket1
    And I send a PUT request to "/api/v2/tickets/{ticket1}" with body:
    """
{
  "subject": "Modified 4"
}
    """
    And the response status code should be 204
    And the "{ticket1}" ticket should have "action_starter" log with detail "event_performer" = "agent"

  Scenario: I modify ticket as User
    Given I have a Ticket record referenced as ticket1
    And I send a PUT request to "/api/v2/tickets/{ticket1}" with body:
    """
{
  "subject": "Modified 4",
  "person":  ~user~
}
    """
    And the response status code should be 204
    And the "{ticket1}" ticket should have "action_starter" log with detail "event_performer" = "user"
