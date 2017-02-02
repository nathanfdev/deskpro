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

  Scenario: I send a message to a conversation
    Given a user with "chewie@falcon.galaxy" email exists
    And I'm authenticated as admin
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

  Scenario: I get the last messages on a conversation
    Given only the following "Chat" records exist:
      | #  | Subject  |
      | c1 | Chat1    |
    And only the following "ChatMessage" records exist:
      | #  | Conversation | Author  | Person name | Content      |
      | m1 | {c1}         | {agent} | agent       | test message |
      | m2 | {c1}         | {agent} | agent       | test         |
    When I send a GET request to "/api/v2/user_chats/{c1}/messages"
    Then the response should be in JSON
    And the JSON node "data[0].content" should be equal to "test"
    And the JSON node "data[1].content" should be equal to "test message"
