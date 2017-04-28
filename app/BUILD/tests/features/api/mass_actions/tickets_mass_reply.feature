@new
Feature: /mass_actions/tickets endpoint for mass reply
  To complete mass reply on tickets list
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
      | t2 | Ticket 2 |

  Scenario: I try set incorrect reply in mass reply for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params": {
    "reply": 1
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.params.fields.reply.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I try to set empty message in mass reply for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params": {
    "reply": {
      "is_agent_note": true
    }
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.params.fields.reply.fields.message.errors[0].code" should be equal to "required"

  Scenario: I send mass reply for ticket with ID=1 (as message)
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~,~t2~],
  "params": {
    "reply": {
      "is_agent_note": 0,
      "message":"<p>Something no <u>interest</u>ing</p>"
    }
  }
}
    """
    Then the response status code should be 204

    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~, ~t2~],
  "params": {
    "reply": {
      "is_agent_note": 1,
      "message":"<p>Something <u>interest</u>ing as agent note</p>"
    }
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0].ticket" should be equal to "{t1}"
    And the JSON node "data[0].message" should be equal to "<p>Something no <u>interest</u>ing</p>"
    And the JSON node "data[0].is_agent_note" should be equal to 0
    And the JSON node "data[1].ticket" should be equal to "{t1}"
    And the JSON node "data[1].message" should be equal to "<p>Something <u>interest</u>ing as agent note</p>"
    And the JSON node "data[1].is_agent_note" should be equal to 1

    When I send a GET request to "/api/v2/tickets/{t2}/messages"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data[0].ticket" should be equal to "{t2}"
    And the JSON node "data[0].message" should be equal to "<p>Something no <u>interest</u>ing</p>"
    And the JSON node "data[0].is_agent_note" should be equal to 0
    And the JSON node "data[1].ticket" should be equal to "{t2}"
    And the JSON node "data[1].message" should be equal to "<p>Something <u>interest</u>ing as agent note</p>"
    And the JSON node "data[1].is_agent_note" should be equal to 1
