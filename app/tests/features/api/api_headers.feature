Feature: JSON API Headers
  In order to inspect response headers
  As a developer
  I need an option to include reponse headers in the json response body

  Background: Empty db
    Given I install the "empty" data set

  Scenario: I do not include the header flag in my request
    When I send a GET request to "/api/v2/sandbox_widgets"

  Scenario: I include the header flag in my request
    When I send a GET request to "/api/v2/sandbox_widgets?include_headers=1"
    Then the JSON node "headers" should exist
    And the JSON node "headers.status-code" should be equal to "200"

