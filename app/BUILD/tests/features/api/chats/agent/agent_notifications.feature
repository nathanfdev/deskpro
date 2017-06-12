@new
Feature: Notifications Api feature
  To work with notifications and action alerts

  Background:
    Given I'm authenticated as "admin"
    And the setting "beta_features.agent_chat" is set to 1

  Scenario: I get basic settings for action-alerts
    When I send a GET request to "/api/v2/notify/setup/action-alerts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.clients" should exist

  Scenario: I send some text to everyone-chat
    Given there are no "AgentChat" records
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "everyone"
}
    """
    Then the response status code should be 201
    And the JSON node "data.chat_type" should be equal to "everyone"

  Scenario: test everyone chat adding messages
    Given only the following "AgentChat" records exist:
      | #             | Type     |
      | everyone-chat | everyone |
    When I send a POST request to "/api/v2/agent_chats/{everyone-chat}/messages" with body:
    """
{
  "message": "This is a TEST message"
}
    """
    Then the response status code should be 201
    And the JSON node "data.chat" should be equal to "{everyone-chat}"
    And the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.message" should be equal to "This is a TEST message"
    # Actually I don't know how to avoid this big test. By this we just checking that endpoint creates proper notifications
    # Make get_messages.php works or make it possible to check created notifications in DB throught some context
    # When I send a GET request to "/get_messages.php"
    # Then the response status code should be 200

