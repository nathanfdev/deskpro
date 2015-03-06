Feature: API Errors
  To know something went wrong
  As a developer
  I need to see error codes

  Background:
    Given I install the api data set

  Scenario: Request with a non-JSON body
    When I send a POST request to "/api/v2/sandbox_widgets" with body:
    """
    foo=bar
    """
    Then the JSON node "code" should be equal to 400
    And the JSON node "message" should exist
    And the response status code should be 400

  Scenario: Invalid JSONP callbacks result in an error
    When I send a GET request to "/api/v2/sandbox_widgets?callback=function"
    Then the JSON node "code" should be equal to 400
    And the JSON node "message" should exist
    Then the response status code should be 400

  Scenario: Request non-existent resource
    When I send a GET request to "/api/v2/sandbox_widgets/124"
    Then the response should be in JSON
    And the response status code should be 404
    And the JSON node "code" should be equal to 404
    And the JSON node "message" should exist

  Scenario: Make an invalid POST
    When I send a POST request to "/api/v2/sandbox_widgets" with body:
    """
    {
      "name": "My Awesome Widget",
      "inventory": "should be a number, but is a string"
    }
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "code" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "errors.children" should have 1 element

  Scenario: Make a POST with a missing field
    When I send a POST request to "/api/v2/sandbox_widgets" with body:
    """
    {
      "inventory": 4
    }
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "code" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "errors.children" should have 1 element