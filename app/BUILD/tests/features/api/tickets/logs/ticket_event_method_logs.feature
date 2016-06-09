@new
Feature: Ticket logs save context

  Background:
    Given I'm authenticated as admin

  Scenario: I check api method
    When I send a "POST" request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket subject"
}
    """
    Then the response status code should be 201
    And the "{lastCreatedId}" ticket should have "ticket_created" log with detail "event_method" = "api"

  Scenario: I check mobile event method
    Given I add "x-deskpro-api-clienttype" header equal to "ios"
    When I send a "POST" request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket subject"
}
    """
    Then the response status code should be 201
    And the "{lastCreatedId}" ticket should have "ticket_created" log with detail "event_method" = "mobile"
