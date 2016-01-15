Feature: Widget Chat

  Background: Fresh database
    Given I install the fresh data set

  Scenario: I try to create a new chat without session code
    When I send a POST request to "/portal/api/chats/create" with body:
    """
    {}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "message" should be equal to "User session not found"

#  Scenario: I create a new chat with wit disabled require login and email validation settings