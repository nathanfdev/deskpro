@new
Feature: Follow location

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject        |
      | t1 | Ticket subject |

  Scenario: I edit ticket w/o location
    When I send a PUT request to "/api/v2/tickets/{t1}"
    Then the response status code should be 204
    And the response should be empty

  Scenario: I follow location
    When I send a PUT request to "/api/v2/tickets/{t1}?follow_location=1"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{t1}"
