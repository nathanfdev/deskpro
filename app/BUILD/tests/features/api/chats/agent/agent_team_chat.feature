@chats @agent-chats
Feature: /agent_chats endpoint
  To check chat with team

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I create chat with team
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "team",
  "participant": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/agent_chats/1"
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.agent_teams" should exist
    And the JSON node "data.agent_teams" should have 1 element
    And the JSON node "data.agent_teams[0]" should be equal to 1

  Scenario: I reopen chat with team
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "team",
  "participant": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 302
    And the header "Location" should be equal to "/api/v2/agent_chats/1"

  Scenario: I get a team chat
    When I send a GET request to "/api/v2/agent_chats/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.id" should be equal to 1

  Scenario: I get an agent chat
    When I send a GET request to "/api/v2/agent_chats/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.id" should be equal to 1
