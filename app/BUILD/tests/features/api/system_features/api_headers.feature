Feature: JSON API Headers
  In order to inspect response headers
  As a developer
  I need an option to include response headers in the json response body

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I do not include the header flag in my request
    When I send a GET request to "/api/v2/user_groups"
    Then the JSON node "headers" should not exist

  Scenario: I include the header flag in my request
    When I send a GET request to "/api/v2/user_groups?include_headers=1"
    Then the JSON node "headers" should exist
    And the JSON node "headers.status-code" should be equal to "200"

  Scenario: Verifying the application/json Content-Type
    When I send a GET request to "/api/v2/people?include_headers=1"
    And the JSON node "headers.content-type" should be equal to "application/json"

