Feature: Follow location

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I follow location
    When I send a PUT request to "/api/v2/tickets/1"
    Then the response status code should be 204
    And the response should be empty

    When I send a PUT request to "/api/v2/tickets/1?follow_location=1"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 1
