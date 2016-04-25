@chats @agent-chats @notifications
Feature: Notifications Api feature
  To work with notifications and action alerts

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
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
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.chat_type" should be equal to "everyone"

    When I send a POST request to "/api/v2/agent_chats/1/messages" with body:
    """
{
  "message": "This is a TEST message"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.chat" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.message" should be equal to "This is a TEST message"

    When I send a GET request to "/api/v2/notify/action-alerts/0"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].data.id" should be equal to 1
    And the JSON node "data[0].data.chat" should be equal to 1
    And the JSON node "data[0].data.person" should be equal to 1
    And the JSON node "data[0].data.message" should be equal to "This is a TEST message"
