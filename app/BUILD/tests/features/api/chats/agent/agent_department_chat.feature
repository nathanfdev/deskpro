Feature: /agent_chats endpoint
  To check chat with department

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I create department chat
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "department",
  "participant": 3
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/agent_chats/1"
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.departments" should have 1 element
    And the JSON node "data.departments[0]" should be equal to 3

  Scenario: I try to create chat with ticket department
    Given I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "department",
  "participant": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.participant.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.participant.errors[0].message" should be equal to "One or more of the given values is invalid."

  # This given is done to avoid data reinstalling, and we can't place this test before previous, because we should check
  # that department was not added
  Scenario: I get an agent chat (department)
    Given I add "admin" usergroup relation "agent_all_perms"
    And I add "admin" usergroup relation "agent_all_safe_perms"
    When I send a GET request to "/api/v2/agent_chats/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.chat_type" should be equal to "department"
