Feature: /tickets endpoint
  To CRUD DeskPRO tickets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve a ticket
    When I send a GET request to "/api/v2/tickets/1"
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "Test"

  @basic
  Scenario: I retrieve list of tickets
    When I send a GET request to "/api/v2/tickets?sort=id&order=desc"
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].subject" should be equal to "Ticket #3"
    And the JSON node "data[1].subject" should be equal to "Ticket #2"

  @basic
  Scenario: I create a ticket
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "department": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.subject" should be equal to "Sample Ticket"

  Scenario: I try to create a ticket providing empty data
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.subject" should exist

  @basic
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

  @basic
  Scenario: I delete a ticket
    When I send a DELETE request to "/api/v2/tickets/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I try to get not existing ticket
    When I send a GET request to "/api/v2/tickets/40404"
    Then the response status code should be 404
