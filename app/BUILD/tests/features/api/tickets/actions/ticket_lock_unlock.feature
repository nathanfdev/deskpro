@new
Feature: /tickets/{id}/actions/(lock|unlock) endpoints
  To CRUD DeskPRO tickets
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And there are no "Permission" records

  Scenario: I lock ticket
    Given the following Ticket records exist:
      | #      | Subject           | Agent | Status        |
      | ticket | First Demo Ticket | {me}  | awaiting_user |
    When I send a PUT request to "/api/v2/tickets/404/actions/lock"
    Then the response status code should be 404

    When I send a PUT request to "/api/v2/tickets/{ticket}/actions/lock"
    Then the response status code should be 204

    When I send a PUT request to "/api/v2/tickets/{ticket}/actions/lock"
    Then the response status code should be 400
    And the JSON node "message" should be equal to "Ticket is already locked"

  Scenario: I lock ticket by another user
    Given "admin@deskpro.dev" admin exists
    And I'm authenticated as "agent"
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"
    And the following Ticket records exist:
      | #      | Subject           | Agent | Status        | locked_by_agent |
      | ticket | First Demo Ticket | {me}  | awaiting_user | {admin}         |

    When I send a PUT request to "/api/v2/tickets/{ticket}/actions/lock"
    Then the response status code should be 403

    Given I add "agent" usergroup relation "agent_all_perms"
    And I add "agent" usergroup relation "agent_all_safe_perms"
    When I send a PUT request to "/api/v2/tickets/{ticket}/actions/lock" with body:
    """
{
  "force": true
}
    """
    Then the response status code should be 204

  Scenario: I unlock ticket by another user
    Given I'm authenticated as "admin"
    And "agent@deskpro.dev" agent exists
    And the following Ticket records exist:
      | #      | Subject           | Agent | Status        | locked_by_agent |
      | ticket | First Demo Ticket | {me}  | awaiting_user | {agent}         |
    When I send a PUT request to "/api/v2/tickets/{ticket}/actions/unlock"
    Then the response status code should be 400
    And the JSON node "message" should be equal to "Ticket is locked by another agent"

    When I send a PUT request to "/api/v2/tickets/{ticket}/actions/unlock" with body:
    """
{
  "force": true
}
    """
    Then the response status code should be 204

  Scenario: I unlock ticket by own agent
    Given I'm authenticated as "admin"
    And the following Ticket records exist:
      | #      | Subject           | Agent | Status        | locked_by_agent |
      | ticket | First Demo Ticket | {me}  | awaiting_user | {me}            |

    When I send a PUT request to "/api/v2/tickets/{ticket}/actions/unlock"
    Then the response status code should be 204
