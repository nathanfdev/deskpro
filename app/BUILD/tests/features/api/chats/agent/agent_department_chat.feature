@new
Feature: /agent_chats endpoint
  To check chat with department

  Background:
    Given I'm authenticated as admin
    And I have a Department record referenced as department

  Scenario: I create department chat
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "department",
  "participant": ~department~
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/agent_chats/{lastCreatedId}"
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.departments" should have 1 element
    And the JSON node "data.departments[0]" should be equal to "{department}"

    When I send a GET request to "/api/v2/agent_chats/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.chat_type" should be equal to "department"

  Scenario: I try to create chat with department w/o permissions
    Given I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "department",
  "participant": ~department~
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.participant.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.participant.errors[0].message" should be equal to "One or more of the given values is invalid."
