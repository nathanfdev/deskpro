@new
Feature: /tickets/{id}/messages endpoint
  I want to check ticket permission groups

  Background:
    Given I'm authenticated as agent
    And I create a "Ticket" and reference it as "ticket"
    And there are no "Permission" records
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"

  Scenario: I have no access to use tickets

    When I send a GET request to "/api/v2/tickets/{ticket}/messages"
    Then the response status code should be 403

    When I send a POST request to "/api/v2/tickets/{ticket}/messages"
    Then the response status code should be 403

  Scenario: I grant use tickets permission
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/tickets/{ticket}/messages"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/tickets/{ticket}/messages"
    Then the response status code should be 403

  Scenario: I grant can reply own permission
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.reply_own" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/tickets/{ticket}/messages"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "my message"
}
    """
    Then the response status code should be 201

  Scenario: I grant modify own permission
    Given the following "TicketMessage" records exist:
      | #  | ticket   | person |
      | tm | {ticket} | {me}   |
    When I send a PUT request to "/api/v2/tickets/{ticket}/messages/{tm}"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/tickets/{ticket}/messages/{tm}"
    Then the response status code should be 403

    Given I set permission "agent_tickets.modify_messages_own" = 1 for "registered" usergroup
    And I set permission "agent_tickets.use" = 1 for "registered" usergroup

    When I send a PUT request to "/api/v2/tickets/{ticket}/messages/{tm}" with body:
    """
{
  "message": "my message"
}
    """
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/tickets/{ticket}/messages/{tm}"
    Then the response status code should be 200

   Scenario: As admin I can modify any ticket message
     Given I'm authenticated as admin
     And "agent@deskpro.dev" agent exists
     And the following "TicketMessage" records exist:
       | #  | ticket   | person  |
       | tm | {ticket} | {agent} |
     When I send a GET request to "/api/v2/tickets/{ticket}/messages"
     Then the response status code should be 200

     When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "my message"
}
    """
     Then the response status code should be 201

     When I send a PUT request to "/api/v2/tickets/{ticket}/messages/{tm}" with body:
   """
{
  "message": "my message"
}
    """
     Then the response status code should be 204

     When I send a DELETE request to "/api/v2/tickets/{ticket}/messages/{tm}"
     Then the response status code should be 200
