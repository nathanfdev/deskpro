Feature: /agent_chats endpoint
  To check chat with agent

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I create chat with agent
    When I send a POST request to "/api/v2/agent_chats" with body:
    """
{
  "type": "agent",
  "participant": 2
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/agent_chats/1"
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.agents" should exist
    And the JSON node "data.agents" should have 2 elements
    And the JSON node "data.agents[0]" should be equal to 2
    And the JSON node "data.agents[1]" should be equal to 1

  Scenario: I reopen chat with agent
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "agent",
  "participant": 2
}
    """
    Then the response status code should be 302
    And the response should be empty
    And the header "Location" should be equal to "/api/v2/agent_chats/1"

  Scenario: I try to create chat with user
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "agent",
  "participant": 3
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.participant.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.participant.errors[0].message" should be equal to "One or more of the given values is invalid."

  Scenario: I get an agent chat
    When I send a GET request to "/api/v2/agent_chats/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.chat_type" should be equal to "agent"

  Scenario: I get chat list
    When I send a GET request to "/api/v2/agent_chats"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1

  Scenario: I try to create a message (failed validation)
    When I send a POST request to "/api/v2/agent_chats/1/messages"
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.message.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a message
    When I send a POST request to "/api/v2/agent_chats/1/messages" with body:
    """
{
  "message": "This is a TEST message",
  "uuid": "216fff40-98d9-11e3-a5e2-0800200c9a61"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.message" should be equal to "This is a TEST message"
    And the JSON node "data.status" should be equal to 0

    When I send a POST request to "/api/v2/agent_chats/1/messages" with body:
    """
{
  "message": "One more",
  "uuid": "216fff40-98d9-11e3-a5e2-0800200c9a62"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.message" should be equal to "One more"
    And the JSON node "data.status" should be equal to 0

  Scenario: I get a message list filtered by search string
    When I send a GET request to "/api/v2/agent_chats/1/messages?search=message"
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].message" should be equal to "This is a TEST message"

    When I send a GET request to "/api/v2/agent_chats/1/messages?search=failure"
    And the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I count messages
    When I send a GET request to "/api/v2/agent_chats/messages/counts?group_by=chat"
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 2
    And the JSON node "data.grouped_by" should be equal to "chat"
    And the JSON node "data.nested" should have 1 element
    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].type" should be equal to "chat"
    And the JSON node "data.nested[0].count" should be equal to 2

  Scenario: I try to mark messages with empty request
    When I send a PUT request to "/api/v2/agent_chats/1/messages/mark"
    Then the response status code should be 400
    And the JSON node "errors.fields.status.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.status.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I mark messages
    When I send a PUT request to "/api/v2/agent_chats/1/messages/mark" with body:
    """
{
  "ids": [1,2],
  "status": 1
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/agent_chats/1/messages?order_by=id&order_dir=asc"
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].status" should be equal to 1
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].status" should be equal to 1

  Scenario: I get a message list for nonexistent chat
    When I send a GET request to "/api/v2/agent_chats/500/messages"
    And the response status code should be 404
    And the JSON node "data" should not exist
