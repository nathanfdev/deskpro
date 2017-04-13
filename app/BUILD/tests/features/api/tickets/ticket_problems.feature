@new
Feature: /ticket_problems endpoint
  To CRUD DeskPRO ticket problems
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And no Problem records exist

  Scenario: I retrieve a list of ticket problems
    Given only the following Problem records exist:
      | #  | Title     | Is Open |
      | p1 | Problem 1 | 1       |
      | p2 | Problem 2 | 1       |
      | p3 | Problem 3 | 0       |

    When I send a GET request to "/api/v2/ticket_problems"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements

    When I send a GET request to "/api/v2/ticket_problems?is_open=1&order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 2 element
    And the JSON node "data[0].id" should be equal to "{p1}"
    And the JSON node "data[1].id" should be equal to "{p2}"

    When I send a GET request to "/api/v2/ticket_problems?is_open=0"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{p3}"

  Scenario: I try to create a new ticket problem with empty request
    When I send a POST request to "/api/v2/ticket_problems"
    Then the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"

  Scenario: I create a new open ticket problem
    When I send a POST request to "/api/v2/ticket_problems" with body:
    """
{
  "title": "Problem 1",
  "is_open": true
}
    """
    Then the response status code should be 201
    And the JSON node "data.title" should be equal to "Problem 1"
    And the JSON node "data.is_open" should be equal to 1

  Scenario: I create a new closed ticket problem
    When I send a POST request to "/api/v2/ticket_problems" with body:
    """
{
  "title": "Problem 2",
  "is_open": false
}
    """
    Then the response status code should be 201
    And the JSON node "data.title" should be equal to "Problem 2"
    And the JSON node "data.is_open" should be equal to 0

  Scenario: I create a new ticket problem with default open status
    When I send a POST request to "/api/v2/ticket_problems" with body:
    """
{
  "title": "Problem 3"
}
    """
    Then the response status code should be 201
    And the JSON node "data.title" should be equal to "Problem 3"
    And the JSON node "data.is_open" should be equal to 1
