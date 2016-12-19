Feature: /ticket_stars endpoint
  To CRUD DeskPRO ticket's stars
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get list of my ticket stars
    When I send a GET request to "/api/v2/ticket_stars"
    Then the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should have 7 element
    And the JSON node "data[0].name" should be equal to "Blue"
    And the JSON node "data[1].name" should be equal to "First custom"
    And the JSON node "data[3].name" should be equal to "Second custom"

  Scenario: I get counts of my ticket flagged by stars
    When I send a GET request to "/api/v2/ticket_stars/counts"
    Then the response status code should be 200
    And the JSON node "data.nested" should have 7 element
    And the JSON node "data.nested[0].count" should be equal to 0
    And the JSON node "data.nested[0].title" should be equal to "Blue"
    And the JSON node "data.nested[1].count" should be equal to 2
    And the JSON node "data.nested[1].title" should be equal to "First custom"

  Scenario: I modify custom name
    When I send a PUT request to "/api/v2/ticket_stars/1"
    Then the response status code should be 204

    When I send a PUT request to "/api/v2/ticket_stars/1" with body:
    """
{
  "name": "blue custom"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_stars"
    And the JSON node "data[0].name" should be equal to "Blue custom"

    When I send a PUT request to "/api/v2/ticket_stars/1" with body:
    """
{
  "name": ""
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_stars"
    And the JSON node "data[0].name" should be equal to "Blue"
