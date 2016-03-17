@basic @tickets
Feature: /tickets endpoint
  To CRUD DeskPRO tickets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a ticket
    When I send a GET request to "/api/v2/tickets/1"
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "Test"

  Scenario: I retrieve list of tickets
    When I send a GET request to "/api/v2/tickets?order_by=id&order_dir=desc"
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].subject" should be equal to "Ticket #3"
    And the JSON node "data[1].subject" should be equal to "Ticket #2"

  Scenario: I try to create a ticket providing empty data
    When I send a POST request to "/api/v2/tickets"
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data.subject" should be equal to "(No Subject)"

  Scenario: I create a ticket
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "department": 1,
  "is_hold": true
}
    """
    Then the response status code should be 201
    And the JSON node "data.subject" should be equal to "Sample Ticket"
    And the JSON node "data.is_hold" should be equal to 1

  Scenario: I modify and retrieve a ticket
    When I send a PUT request to "/api/v2/tickets/1" with body:
    """
{
  "subject": "Modified subject"
}
    """
    And I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the JSON node "data.subject" should be equal to "Modified subject"

  Scenario: I delete a ticket
    When I send a DELETE request to "/api/v2/tickets/5"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I try to get deleted ticket
    When I send a GET request to "/api/v2/tickets/5"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "deleted"

  Scenario: I try to get not existing ticket
    When I send a GET request to "/api/v2/tickets/40404"
    Then the response status code should be 404

  Scenario: I try to modify not existing ticket
    When I send a PUT request to "/api/v2/tickets/40404" with body:
    """
{
  "subject": "Modified subject"
}
    """
    Then the response status code should be 404
