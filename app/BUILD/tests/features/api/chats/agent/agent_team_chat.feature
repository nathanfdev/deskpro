@new
Feature: /agent_chats endpoint
  To check chat with team

  Background:
    Given I'm authenticated as "agent"
    And the setting "beta_features.agent_chat" is set to 1


  Scenario: I create chat with team
    Given the following "AgentTeam" records exist:
      | #    | Name      | Members   |
      | team | Some team | [{agent}] |
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "team",
  "participant": ~team~
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/agent_chats/{lastCreatedId}"
    And the JSON node "data.agent_teams" should exist
    And the JSON node "data.agent_teams" should have 1 element
    And the JSON node "data.agent_teams[0]" should be equal to "{team}"

  Scenario: I get a team chat
    Given the following "AgentTeam" records exist:
      | #    | Name      | Members   |
      | team | Some team | [{agent}] |
    And the following "AgentChat" records exist:
      | #                | Type |
      | agent-team-chat | team |
    And the following "AgentChatParticipant" records exist:
      | Chat              | Team   |
      | {agent-team-chat} | {team} |
    When I send a GET request to "/api/v2/agent_chats/{agent-team-chat}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.chat_type" should be equal to "team"

  Scenario: I reopen chat with team
    Given the following "AgentTeam" records exist:
      | #    | Name      | Members   |
      | team | Some team | [{agent}] |
    And the following "AgentChat" records exist:
      | #                | Type |
      | agent-team-chat | team |
    And the following "AgentChatParticipant" records exist:
      | Chat              | Team   |
      | {agent-team-chat} | {team} |
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "team",
  "participant": ~team~
}
    """
    Then the response status code should be 302
    And the response should be empty
    And the header "Location" should be equal to "/api/v2/agent_chats/{agent-team-chat}"
