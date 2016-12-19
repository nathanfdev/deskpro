@new
Feature: Ticket messages logs

  Background:
    Given I'm authenticated as admin
    And I create a Ticket record and reference it as ticket

  Scenario: I add a ticket message and check the logs
    When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "Hello"
}
    """
    Then the response status code should be 201
    And the "{ticket}" ticket should have "message_created" log

  Scenario: I remove a message and check the logs
    Given I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "Hello"
}
    """
    When I send a DELETE request to "/api/v2/tickets/{ticket}/messages/{lastCreatedId}"
    Then the response status code should be 200
    And the "{ticket}" ticket should have "message_removed" log

