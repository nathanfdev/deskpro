@new
Feature: /agent_chats endpoint
  To check chat with agent

  Background:
    Given I'm authenticated as admin

  Scenario: I create chat with agent
    Given an agent with "darthvader@empire.galaxy" email exists
    When I send a POST request to "/api/v2/agent_chats" with body:
    """
{
  "type": "agent",
  "participant": ~darthvader@empire.galaxy~
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/agent_chats/{lastCreatedId}"
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.date_last_message" should be equal to "null"
    And the JSON node "data.agents" should exist
    And the JSON node "data.agents" should have 2 elements
    And the JSON node "data.agents[0]" should be equal to "{darthvader@empire.galaxy}"
    And the JSON node "data.agents[1]" should be equal to "{admin}"

  Scenario: I reopen chat with agent
    Given an agent with "darthvader@empire.galaxy" email exists
    And only the following "AgentChat" records exist:
      | #                | Type  |
      | agent-agent-chat | agent |
    And only the following "AgentChatParticipant" records exist:
      | #  | Chat               | Person                     |
      | p1 | {agent-agent-chat} | {admin}                    |
      | p2 | {agent-agent-chat} | {darthvader@empire.galaxy} |

    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "agent",
  "participant": ~darthvader@empire.galaxy~
}
    """
    Then the response status code should be 302
    And the response should be empty
    And the header "Location" should be equal to "/api/v2/agent_chats/{agent-agent-chat}"

  Scenario: I try to create chat with user
    Given a user with "chewie@falcon.galaxy" email exists
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "agent",
  "participant": ~chewie@falcon.galaxy~
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.participant.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.participant.errors[0].message" should be equal to "One or more of the given values is invalid."

  Scenario: I get an agent chat
    Given an agent with "darthvader@empire.galaxy" email exists
    And only the following "AgentChat" records exist:
      | #                | Type  |
      | agent-agent-chat | agent |
    And only the following "AgentChatParticipant" records exist:
      | #  | Chat               | Person                     |
      | p1 | {agent-agent-chat} | {admin}                    |
      | p2 | {agent-agent-chat} | {darthvader@empire.galaxy} |
    When I send a GET request to "/api/v2/agent_chats/{agent-agent-chat}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.id" should be equal to "{agent-agent-chat}"
    And the JSON node "data.chat_type" should be equal to "agent"

  Scenario: I get chat list
    Given an agent with "darthvader@empire.galaxy" email exists
    And only the following "AgentChat" records exist:
      | #                | Type  |
      | agent-agent-chat | agent |
    And only the following "AgentChatParticipant" records exist:
      | #  | Chat               | Person                     |
      | p1 | {agent-agent-chat} | {admin}                    |
      | p2 | {agent-agent-chat} | {darthvader@empire.galaxy} |
    When I send a GET request to "/api/v2/agent_chats"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{agent-agent-chat}"
