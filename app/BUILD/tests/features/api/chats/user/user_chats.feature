Feature: /user_chats endpoint
  To retrieve DeskPRO agents
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve chat list
    When I send a GET request to "/api/v2/user_chats"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data" should have 5 elements
    And the JSON node "data[0].subject" should be equal to "Test chat 5"
    And the JSON node "data[1].subject" should be equal to "Test chat 4"
    And the JSON node "data[2].subject" should be equal to "Test chat 3"
    And the JSON node "data[3].subject" should be equal to "Test chat 2"
    And the JSON node "data[4].subject" should be equal to "Test chat 1"

  Scenario: I get chat
    When I send a GET request to "/api/v2/user_chats/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "Test chat 1"
