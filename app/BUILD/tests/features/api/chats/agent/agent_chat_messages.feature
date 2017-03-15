@new
Feature: /agent_chats endpoint
  To check chat with agent

  Background:
    Given I'm authenticated as "agent"
    And the setting "beta_features.agent_chat" is set to 1
    And an agent with "james@mi7.uk" email exists

    And the following "AgentChat" records exist:
      | #                | Type  |
      | agent-agent-chat | agent |
    And the following "AgentChatParticipant" records exist:
      | #  | Chat               | Person         |
      | p1 | {agent-agent-chat} | {agent}        |
      | p2 | {agent-agent-chat} | {james@mi7.uk} |


  Scenario: I try to create a message (failed validation)
    When I send a POST request to "/api/v2/agent_chats/{agent-agent-chat}/messages"
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.message.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a message
    When I send a POST request to "/api/v2/agent_chats/{agent-agent-chat}/messages" with body:
  """
  {
    "message": "This is a TEST message",
    "uuid": "216fff40-98d9-11e3-a5e2-0800200c9a61"
  }
      """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.message" should be equal to "This is a TEST message"
    And the JSON node "data.status" should be equal to 0

  Scenario: I get a message list filtered by search string
    Given only the following "AgentChatMessage" records exist:
      | #  | Chat               | Person  | UUID | Person name | Message      | Status |
      | m1 | {agent-agent-chat} | {agent} | 1111 | agent       | test message | 0      |
      | m2 | {agent-agent-chat} | {agent} | 1112 | agent       | test         | 0      |
    When I send a GET request to "/api/v2/agent_chats/{agent-agent-chat}/messages?search=message"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].message" should be equal to "test message"

  Scenario: I get a message list filtered by search string
    Given only the following "AgentChatMessage" records exist:
      | #  | Chat               | Person  | UUID | Person name | Message      | Status |
      | m1 | {agent-agent-chat} | {agent} | 1111 | agent       | test message | 0      |
      | m2 | {agent-agent-chat} | {agent} | 1112 | agent       | test         | 0      |
    When I send a GET request to "/api/v2/agent_chats/{agent-agent-chat}/messages?search=failure"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I count messages
    Given only the following "AgentChatMessage" records exist:
      | #  | Chat               | Person  | UUID | Person name | Message      | Status |
      | m1 | {agent-agent-chat} | {agent} | 1111 | agent       | test message | 0      |
      | m2 | {agent-agent-chat} | {agent} | 1112 | agent       | test         | 0      |
    When I send a GET request to "/api/v2/agent_chats/messages/counts?group_by=chat"
    Then the response status code should be 200
    And the JSON node "data.count" should be equal to 2
    And the JSON node "data.grouped_by" should be equal to "chat"
    And the JSON node "data.nested" should have 1 element
    And the JSON node "data.nested[0].id" should be equal to "{agent-agent-chat}"
    And the JSON node "data.nested[0].type" should be equal to "chat"
    And the JSON node "data.nested[0].count" should be equal to 2

  Scenario: I try to mark messages with empty request
    When I send a PUT request to "/api/v2/agent_chats/{agent-agent-chat}/messages/mark"
    Then the response status code should be 400
    And the JSON node "errors.fields.status.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.status.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I mark messages
    Given only the following "AgentChatMessage" records exist:
      | #  | Chat               | Person  | UUID | Person name | Message      | Status |
      | m1 | {agent-agent-chat} | {agent} | 1111 | agent       | test message | 0      |
      | m2 | {agent-agent-chat} | {agent} | 1112 | agent       | test         | 0      |
    When I send a PUT request to "/api/v2/agent_chats/{agent-agent-chat}/messages/mark" with body:
  """
  {
    "ids": [~m1~,~m2~],
    "status": 1
  }
      """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/agent_chats/{agent-agent-chat}/messages?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{m1}"
    And the JSON node "data[0].status" should be equal to 1
    And the JSON node "data[1].id" should be equal to "{m2}"
    And the JSON node "data[1].status" should be equal to 1

  Scenario: I mark all messages as read
    Given only the following "AgentChatMessage" records exist:
      | #  | Chat               | Person         | UUID | Person name | Message      | Status |
      | m1 | {agent-agent-chat} | {james@mi7.uk} | 1111 | agent       | test message | 0      |
      | m2 | {agent-agent-chat} | {james@mi7.uk} | 1112 | agent       | test         | 0      |
    When I send a PUT request to "/api/v2/agent_chats/{agent-agent-chat}/messages/mark_all"
    Then the response status code should be 204

    When I send a GET request to "/api/v2/agent_chats/{agent-agent-chat}/messages?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{m1}"
    And the JSON node "data[0].status" should be equal to 2
    And the JSON node "data[1].id" should be equal to "{m2}"
    And the JSON node "data[1].status" should be equal to 2

  Scenario: I get a message list for nonexistent chat
    When I send a GET request to "/api/v2/agent_chats/500/messages"
    Then the response status code should be 404
    And the JSON node "data" should not exist
