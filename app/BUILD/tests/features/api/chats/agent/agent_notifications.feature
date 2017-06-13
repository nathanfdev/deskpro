@new
Feature: Notifications Api feature
  To work with notifications and action alerts

  Background:
    Given I'm authenticated as "admin"
    And the setting "beta_features.agent_chat" is set to 1
    And there are no "ActionAlert" records
    And there are no "Notification" records
    And there are no "AgentChatMessage" records

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
    # no notification for me, I'm not notification target for my message
    And there should be 0 notifications for me
    # should receive message back with ids and backend-processed back, should not receive read.notifications.alert
    And there should be 1 action_alert for me

  Scenario: Check proper notifications count
    Given an agent with "james@mi7.uk" email exists
    Given an agent with "jhonny@mi7.uk" email exists
    And the following "AgentChat" records exist:
      | #                | Type  | Name       |
      | agent-group-chat | group | Test group |
    And the following "AgentChatParticipant" records exist:
      | #  | Chat               | Person         |
      | p1 | {agent-group-chat} | {me}           |
      | p2 | {agent-group-chat} | {james@mi7.uk} |
      | p2 | {agent-group-chat} | {jhonny@mi7.uk} |

    When I send a POST request to "/api/v2/agent_chats/{agent-group-chat}/messages" with body:
    """
{
  "message": "This is a TEST message"
}
    """
    Then the response status code should be 201
    #only for one who's online
    And there should be 2 notifications
    And there should be 5 action_alerts