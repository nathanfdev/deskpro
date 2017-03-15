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
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "everyone"
}
    """
    Then the response status code should be 201
    And the JSON node "data.chat_type" should be equal to "everyone"

  Scenario: test everyone chat adding messages
    Given the following "AgentChat" records exist:
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
    When I send a GET request to "/api/v2/notify/action-alerts/0"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].data" should have 3 elements
    And the JSON node "data[0].data.data.chat" should be equal to "{everyone-chat}"
    And the JSON node "data[0].data.data.person" should be equal to "{admin}"
    And the JSON node "data[0].data.data.message" should be equal to "This is a TEST message"
    And the JSON node "data[0].data.linked.agent_chat.{everyone-chat}.id" should be equal to "{everyone-chat}"
