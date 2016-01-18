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
    When I send a POST request to "api/v2/agent_chats/start" with body:
    """
      {
        "type" : "everyone",
        "id": 0
      }
    """
    When I send a POST request to "/api/v2/agent_chats/1/messages" with body:
    """
      {
        "message": "This is a TEST message"
      }
    """
    When I send a GET request to "/api/v2/notify/action-alerts/0"
    Then the response status code should be 200
    And the JSON node data should exist