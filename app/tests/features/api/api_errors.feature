Feature: API Errors
  To know something went wrong
  As a developer
  I need to see error codes

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: Request with a non-JSON body
    When I send a POST request to "/api/v2/sandbox_widgets" with body:
    """
    foo=bar
    """
    Then the response should be in JSON
    Then the JSON node "status" should be equal to 400
    Then the JSON node "code" should be equal to "invalid_json_body"
    And the JSON node "message" should exist
    And the response status code should be 400

  Scenario: Invalid JSONP callbacks result in an error
    When I send a GET request to "/api/v2/sandbox_widgets?callback=function"
    Then the response should be in JSON
    Then the response status code should be 400
    And the JSON node "status" should be equal to 400
    And the JSON node "code" should be equal to "invalid_jsonp_callback"
    And the JSON node "message" should exist

  Scenario: Request non-existent resource
    When I send a GET request to "/api/v2/sandbox_widgets/124"
    Then the response should be in JSON
    Then the response status code should be 404
    And the JSON node "status" should be equal to 404
    And the JSON node "code" should be equal to "not_found"
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
    And the JSON node "status" should be equal to 400
    And the JSON node "code" should be equal to "invalid_input"
    And the JSON node "message" should exist
    And the JSON node "errors" should have 1 element
    And the JSON node "errors.inventory.code" should be equal to "invalid_type"
    And the JSON node "errors.inventory.message" should exist

  Scenario: Make a POST with a missing field
    When I send a POST request to "/api/v2/sandbox_widgets" with body:
    """
    {
      "inventory": 4
    }
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "status" should be equal to 400
    And the JSON node "code" should be equal to "invalid_input"
    And the JSON node "message" should exist
    And the JSON node "errors" should have 1 element
    And the JSON node "errors.name.code" should be equal to "required"
    And the JSON node "errors.name.message" should exist