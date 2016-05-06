@tickets
Feature: /ticket_problems endpoint
  To CRUD DeskPRO ticket problems
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve a list of ticket problems
    When I send a GET request to "/api/v2/ticket_problems"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I try to create a new ticket problem with empty request
    When I send a POST request to "/api/v2/ticket_problems"
    Then the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a new ticket problem
    When I send a POST request to "/api/v2/ticket_problems" with body:
    """
{
  "title": "Problem 1",
  "is_open": true
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.title" should be equal to "Problem 1"
    And the JSON node "data.is_open" should be equal to 1

    When I send a POST request to "/api/v2/ticket_problems" with body:
    """
{
  "title": "Problem 2",
  "is_open": false
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.title" should be equal to "Problem 2"
    And the JSON node "data.is_open" should be equal to 0

    When I send a POST request to "/api/v2/ticket_problems" with body:
    """
{
  "title": "Problem 3"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.title" should be equal to "Problem 3"
    And the JSON node "data.is_open" should be equal to 1

  Scenario: I retrieve a list of opened ticket problems
    When I send a GET request to "/api/v2/ticket_problems?is_open=1"
    Then the response status code should be 200
    And the JSON node "data" should have 2 element

  Scenario: I retrieve a list of opened ticket problems
    When I send a GET request to "/api/v2/ticket_problems?is_open=0"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].title" should be equal to "Problem 2"
