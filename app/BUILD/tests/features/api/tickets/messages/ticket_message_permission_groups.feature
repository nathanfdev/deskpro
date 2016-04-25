@tickets
Feature: /tickets/{id}/messages endpoint
  I want to check ticket permission groups

  Background:
    Given I install the api data set
    And my request is authenticated
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

  @reinstall
  Scenario: I have no access to use tickets
    When I send a GET request to "/api/v2/tickets/2/messages"
    Then the response status code should be 403

    When I send a POST request to "/api/v2/tickets/2/messages"
    Then the response status code should be 403

  Scenario: I grant use tickets permission
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/tickets/2/messages"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/tickets/2/messages"
    Then the response status code should be 403

  Scenario: I grant can reply own permission
    Given I set permission "agent_tickets.reply_own" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/tickets/2/messages"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/tickets/2/messages" with body:
    """
{
  "message": "my message"
}
    """
    Then the response status code should be 201

  Scenario: I grant modify own permission
    When I send a PUT request to "/api/v2/tickets/2/messages/1"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/tickets/2/messages/1"
    Then the response status code should be 403

    Given I set permission "agent_tickets.modify_messages_own" = 1 for "registered" usergroup

    When I send a PUT request to "/api/v2/tickets/2/messages/1"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/tickets/2/messages/1"
    Then the response status code should be 200
