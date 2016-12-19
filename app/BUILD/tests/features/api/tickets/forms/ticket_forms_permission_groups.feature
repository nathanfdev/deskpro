@new
Feature: /ticket_forms endpoint
  I want to check permission groups

  Background:
    Given I'm authenticated as agent
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
      | d2 | Department 2 | 1                  |
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario: I have no ticket permissions
    When I send a POST request to "/api/v2/ticket_forms/agent"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}"
    Then the response status code should be 403

  Scenario: I grant tickets create permission
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.create" = 1 for "registered" usergroup
    And I grant the "{d1}" department permission of "tickets" app for "agent"

    When I send a POST request to "/api/v2/ticket_forms/agent"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}"
    Then the response status code should be 403

  Scenario: I grant tickets edit permission
    Given I set permission "agent_tickets.modify_own" = 1 for "registered" usergroup
    And I grant the "{d1}" department permission of "tickets" app for "agent"
    And the "{t1}" record "person" prop is equal to "{agent}"

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}"
    Then the response status code should be 204
