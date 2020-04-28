@new
Feature: Ticket permission groups

  Background:
    Given no Ticket records exist
    And I'm authenticated as agent
    And I have only default brand
    And only the following Department records exist:
      | #  | Title      | Brands           | Is Tickets Enabled |
      | d1 | Department | [{defaultBrand}] | 1                  |
    And I have the following Ticket records:
      | #  | Agent   | Department |
      | t1 | {agent} | {d1}       |
      | t2 | NULL    | {d1}       |
    And there are no "Permission" records
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"

  Scenario: I have no access to use tickets
    When I send a GET request to "/api/v2/tickets"
    Then the response status code should be 403

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 403

    When I send a POST request to "/api/v2/tickets"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/tickets/{t1}"
    Then the response status code should be 403

    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "ticket": {
    "status": "pending",
    "subject": "New Ticket 1 Subject"
  }
}
    """
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/tickets/{t1}"
    Then the response status code should be 403

  Scenario: I grant use tickets permission
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/tickets"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/{t2}"
    Then the response status code should be 403

    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "ticket": {
    "status": "pending",
    "subject": "New Ticket 1 Subject"
  }
}
    """
    Then the response status code should be 403

    When I send a POST request to "/api/v2/tickets"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/tickets/{t1}"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/tickets/{t1}"
    Then the response status code should be 403

  Scenario: I grant tickets create permission
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.create" = 1 for "registered" usergroup
    And I grant the "{d1}" department permission of "tickets" app for "agent"

    When I send a GET request to "/api/v2/tickets"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/tickets"
    Then the response status code should be 201

    When I send a PUT request to "/api/v2/tickets/{t1}"
    Then the response status code should be 403

    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "ticket": {
    "status": "pending",
    "subject": "New Ticket 1 Subject"
  }
}
    """
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/tickets/{t1}"
    Then the response status code should be 403

  Scenario: I grant tickets modify own permission
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.create" = 1 for "registered" usergroup
    And I set permission "agent_tickets.modify_own" = 1 for "registered" usergroup
    And I set permission "agent_tickets.reply_own" = 1 for "registered" usergroup
    And I grant the "{d1}" department permission of "tickets" app for "agent"

    When I send a GET request to "/api/v2/tickets"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/tickets"
    Then the response status code should be 201

    When I send a PUT request to "/api/v2/tickets/{t1}"
    Then the response status code should be 204

    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "ticket": {
    "status": "pending",
    "subject": "New Ticket 1 Subject"
  }
}
    """
    Then the response status code should be 201

    When I send a DELETE request to "/api/v2/tickets/{t1}"
    Then the response status code should be 403

  Scenario: I grant ticket delete permissions
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.create" = 1 for "registered" usergroup
    And I set permission "agent_tickets.modify_own" = 1 for "registered" usergroup
    And I set permission "agent_tickets.delete_own" = 1 for "registered" usergroup
    And I grant the "{d1}" department permission of "tickets" app for "agent"

    When I send a GET request to "/api/v2/tickets"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/tickets"
    Then the response status code should be 201

    When I send a PUT request to "/api/v2/tickets/{t1}"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200

  Scenario: As admin I can do with ticket whatever I want
    Given I'm authenticated as admin

    When I send a GET request to "/api/v2/tickets"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/tickets"
    Then the response status code should be 201

    When I send a PUT request to "/api/v2/tickets/{t1}"
    Then the response status code should be 204

    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "ticket": {
    "status": "pending",
    "subject": "New Ticket 1 Subject"
  }
}
    """
    Then the response status code should be 201

    When I send a DELETE request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200

  Scenario: I don't have permissions to modify ticket and when posting a message trying to modify the ticket as well
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.create" = 1 for "registered" usergroup
    And I set permission "agent_tickets.modify_own" = 0 for "registered" usergroup
    And I set permission "agent_tickets.reply_own" = 1 for "registered" usergroup
    And I grant the "{d1}" department permission of "tickets" app for "agent"

    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "ticket": {
    "status": "pending",
    "subject": "New Ticket 1 Subject"
  }
}
    """
    Then the response status code should be 403

  Scenario: I don't have permissions to modify ticket and just try to add a message
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.create" = 1 for "registered" usergroup
    And I set permission "agent_tickets.modify_own" = 0 for "registered" usergroup
    And I set permission "agent_tickets.reply_own" = 1 for "registered" usergroup
    And I grant the "{d1}" department permission of "tickets" app for "agent"

    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message"
}
    """
    Then the response status code should be 201

  Scenario: I don't have permissions to modify ticket and just try to add a note
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.create" = 1 for "registered" usergroup
    And I set permission "agent_tickets.modify_own" = 0 for "registered" usergroup
    And I set permission "agent_tickets.reply_own" = 0 for "registered" usergroup
    And I set permission "agent_tickets.modify_notes_own" = 1 for "registered" usergroup
    And I grant the "{d1}" department permission of "tickets" app for "agent"

    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "is_note": 1
}
    """
    Then the response status code should be 201

  Scenario: I don't have permissions to modify ticket and when adding a note trying to modify the ticket as well
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.create" = 1 for "registered" usergroup
    And I set permission "agent_tickets.modify_own" = 0 for "registered" usergroup
    And I set permission "agent_tickets.reply_own" = 0 for "registered" usergroup
    And I set permission "agent_tickets.modify_notes_own" = 1 for "registered" usergroup
    And I grant the "{d1}" department permission of "tickets" app for "agent"

    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "is_note": 1,
  "ticket": {
    "status": "pending",
    "subject": "New Ticket 1 Subject"
  }
}
    """
    Then the response status code should be 403
