@tickets
Feature: /tickets endpoint
  To check excerpt sideloading
  Agent notes should be included only for agents

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I create a ticket with agent note
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "fields": {
    "6": "some custom text"
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 5

    When I send a POST request to "/api/v2/tickets/5/messages" with body:
    """
{
  "message": "Message"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1

    When I send a POST request to "/api/v2/tickets/5/messages" with body:
    """
{
  "message": "Note",
  "is_note": true
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 2

  Scenario: I check excerpt
    When I send a GET request to "/api/v2/tickets/5?include=ticket_excerpt"
    Then the JSON node "linked.ticket_excerpt.5.message_id" should be equal to "2"
    And the JSON node "linked.ticket_excerpt.5.excerpt" should be equal to "Note"
