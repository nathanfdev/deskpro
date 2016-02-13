Feature: Agent Chats api service
  To work with chats, send messages
  Search in chat history

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I get chat list
    When I send a GET request to "/api/v2/agent_chats"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist

  Scenario: I create chat with agent
    When I send a POST request to "api/v2/agent_chats/start" with body:
    """
      {
        "type" : "agent",
        "id": 3
      }
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/agent_chats/1"
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.agents" should exist
    And the JSON node "data.agents" should have 2 elements
    And the JSON node "data.agents[0]" should be equal to 3
    And the JSON node "data.agents[1]" should be equal to 1

  Scenario: I create same chat with agent
    When I send a POST request to "api/v2/agent_chats/start" with body:
    """
      {
        "type" : "agent",
        "id": 3
      }
    """
    Then the response should be in JSON
    And the response status code should be 302
    And the header "Location" should be equal to "/api/v2/agent_chats/1"

  Scenario: I create chat with team
    When I send a POST request to "api/v2/agent_chats/start" with body:
    """
      {
        "type" : "team",
        "id": 1
      }
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/agent_chats/2"
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.agent_teams" should exist
    And the JSON node "data.agent_teams" should have 1 element
    And the JSON node "data.agent_teams[0]" should be equal to 1


  Scenario: I get a chat
    When I send a GET request to "/api/v2/agent_chats/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.id" should be equal to 1

  Scenario: I create a message
    When I send a POST request to "/api/v2/agent_chats/1/messages" with body:
    """
      {
        "message": "This is a TEST message"
      }
    """
    And the response status code should be 201
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.message" should be equal to "This is a TEST message"

  Scenario: I get a message list
    When I send a GET request to "/api/v2/agent_chats/1/messages?search=message"
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data[0]" should exist
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].message" should be equal to "This is a TEST message"
    And the JSON node "data" should have 1 element

  Scenario: I get a message list
    When I send a GET request to "/api/v2/agent_chats/1/messages?search=failure"
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 0 elements

  Scenario: I get a message list for unexistent chat
    When I send a GET request to "/api/v2/agent_chats/500/messages"
    And the response status code should be 404
    And the JSON node "data" should not exist