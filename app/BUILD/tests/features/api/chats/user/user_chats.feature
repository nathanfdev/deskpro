@new
Feature: /user_chats endpoint
  To retrieve DeskPRO agents
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as agent
    And I have only default brand
    And I have permissions to use agent_chat
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Chat Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1               |
      | d2 | Department 2 | [{defaultBrand}] | 0               |
    And only the following Chat records exist:
      | #  | subject |
      | c1 | Chat1   |
      | c2 | Chat2   |
    And I grant the "{d1}" department permission of chat app for usergroup everyone
    And I grant the "{d2}" department permission of chat app for usergroup everyone

  Scenario: I retrieve chat list
    When I send a GET request to "/api/v2/user_chats"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].subject" should be equal to "Chat2"
    And the JSON node "data[1].subject" should be equal to "Chat1"

  Scenario: I get chat
    When I send a GET request to "/api/v2/user_chats/{c1}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "Chat1"

  Scenario: I create a chat with agent
    Given an agent with "darthvader@empire.galaxy" email exists
    And a user with "chewie@falcon.galaxy" email exists
    When I send a POST request to "/api/v2/user_chats" with body:
    """
{
  "subject": "Test Subject",
  "agent": "darthvader@empire.galaxy",
  "person": "chewie@falcon.galaxy",
  "chat_department": ~d1~
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/user_chats/{lastCreatedId}"
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.agent" should be equal to "{darthvader@empire.galaxy}"
    And the JSON node "data.subject" should be equal to "Test Subject"
    And the JSON node "data.subject_line" should be equal to "Test Subject"

  Scenario: I can not create a chat if the department in not chat enabled
    Given an agent with "darthvader@empire.galaxy" email exists
    And a user with "chewie@falcon.galaxy" email exists
    When I send a POST request to "/api/v2/user_chats" with body:
    """
{
  "subject": "Test Subject",
  "agent": "darthvader@empire.galaxy",
  "person": "chewie@falcon.galaxy",
  "chat_department": ~d2~
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors" should exist

  Scenario: I assign a new agent to a conversation
    Given an agent with "darthvader@empire.galaxy" email exists
    And only the following "Chat" records exist:
      | #  | Subject  |
      | c1 | Chat1    |

    When I send a PUT request to "api/v2/user_chats/{c1}/assign/{darthvader@empire.galaxy}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.id" should be equal to "{c1}"

  Scenario: I send a message to a conversation
    Given a user with "chewie@falcon.galaxy" email exists
    And I'm authenticated as admin
    And only the following "Chat" records exist:
      | #  | Subject  |
      | c1 | Chat1    |

    When I send a POST request to "/api/v2/user_chats/{c1}/messages" with body:
    """
{
  "content": "Test Message",
  "author": "chewie@falcon.galaxy"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node data should exist
    And the JSON node "data.author_id" should be equal to "{chewie@falcon.galaxy}"

  Scenario: I can pull last messages for a conversation
    Given a user with "chewie@falcon.galaxy" email exists
    When I send a GET request to "/api/v2/user_chats/{c1}/messages"
    Then the response should be in JSON
    And print last response


