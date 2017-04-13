@new
Feature: /mass_actions/tickets endpoint for mass reply
  To complete mass reply on tickets list
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject   | Status       |
      | t1 | Ticket 1 | awaiting_user |
      | t2 | Ticket 2 | awaiting_user |

  Scenario: I try set incorrect reply in mass reply for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params":{"reply":1}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "reply" with value "1" is expected to be of type "array", but is of type "string".'

  Scenario: I try to set empty message in mass reply for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params":{"reply":{"isAgentNote":true}}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The required option "message" is missing.'


  Scenario: I send mass reply for tickets
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~,~t2~],
  "params": {
    "reply": {
      "isAgentNote":0,
      "message":"<p>Something no <u>interest</u>ing</p>"
    }
  }
}
    """
    Then the response status code should be 200

    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~,~t2~],
  "params": {
    "reply": {
      "isAgentNote":1,
      "message":"<p>Something <u>interest</u>ing as agent note</p>"
    }
  }
}
    """
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0].ticket" should be equal to "{t1}"
    And the JSON node "data[0].message" should be equal to "<p>Something no <u>interest</u>ing</p>"
    And the JSON node "data[0].creation_system" should be equal to "web.agent"
    And the JSON node "data[0].is_agent_note" should be equal to 0
    And the JSON node "data[1].ticket" should be equal to "{t1}"
    And the JSON node "data[1].message" should be equal to "<p>Something <u>interest</u>ing as agent note</p>"
    And the JSON node "data[1].creation_system" should be equal to "web.agent"
    And the JSON node "data[1].is_agent_note" should be equal to 1

    When I send a GET request to "/api/v2/tickets/{t2}/messages"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data[0].ticket" should be equal to "{t2}"
    And the JSON node "data[0].message" should be equal to "<p>Something no <u>interest</u>ing</p>"
    And the JSON node "data[0].creation_system" should be equal to "web.agent"
    And the JSON node "data[0].is_agent_note" should be equal to 0
    And the JSON node "data[1].ticket" should be equal to "{t2}"
    And the JSON node "data[1].message" should be equal to "<p>Something <u>interest</u>ing as agent note</p>"
    And the JSON node "data[1].creation_system" should be equal to "web.agent"
    And the JSON node "data[1].is_agent_note" should be equal to 1
