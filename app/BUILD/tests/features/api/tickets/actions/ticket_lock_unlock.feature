@tickets
Feature: /tickets/{id}/actions/(lock|unlock) endpoints
  To CRUD DeskPRO tickets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I lock ticket
    When I send a PUT request to "/api/v2/tickets/404/actions/lock"
    Then the response status code should be 404

    When I send a PUT request to "/api/v2/tickets/1/actions/lock"
    Then the response status code should be 204

    When I send a PUT request to "/api/v2/tickets/1/actions/lock"
    Then the response status code should be 400
    And the JSON node "message" should be equal to "Ticket is already locked"

  Scenario: I lock ticket by another user
    Given my request is authenticated to "agent"
    When I send a PUT request to "/api/v2/tickets/1/actions/lock"
    Then the response status code should be 403

    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.view_unassigned" = 1 for "registered" usergroup
    And I set permission "agent_tickets.modify_unassigned" = 1 for "registered" usergroup
    When I send a PUT request to "/api/v2/tickets/1/actions/lock" with body:
    """
{
  "force": true
}
    """
    Then the response status code should be 204

  Scenario: I unlock ticket by another user
    Given my request is authenticated to "admin"
    When I send a PUT request to "/api/v2/tickets/1/actions/unlock"
    Then the response status code should be 400
    And the JSON node "message" should be equal to "Ticket is locked by another agent"

    When I send a PUT request to "/api/v2/tickets/1/actions/unlock" with body:
    """
{
  "force": true
}
    """
    Then the response status code should be 204

  Scenario: I unlock ticket by own agent
    When I send a PUT request to "/api/v2/tickets/1/actions/lock"
    Then the response status code should be 204

    When I send a PUT request to "/api/v2/tickets/1/actions/unlock"
    Then the response status code should be 204
