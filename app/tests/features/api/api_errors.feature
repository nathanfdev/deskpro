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
    And the response status code should be 400
    Then the JSON node "status" should be equal to 400
    Then the JSON node "code" should be equal to "invalid_json_body"
    And the JSON node "message" should be equal to "The request JSON body is not valid JSON."

  Scenario: Invalid JSONP callbacks result in an error
    When I send a GET request to "/api/v2/sandbox_widgets?callback=function"
    Then the response should be in JSON
    Then the response status code should be 400
    And the JSON node "status" should be equal to 400
    And the JSON node "code" should be equal to "invalid_jsonp_callback"
    And the JSON node "message" should be equal to "The JSONP callback parameter is invalid. Please use a JSONP callback is is not a Javascript reserved word."

  Scenario: Request non-existent resource
    When I send a GET request to "/api/v2/sandbox_widgets/124"
    Then the response should be in JSON
    Then the response status code should be 404
    And the JSON node "status" should be equal to 404
    And the JSON node "code" should be equal to "not_found"
    And the JSON node "message" should be equal to "The requested resource was not found."

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
    And the JSON node "message" should be equal to "Request input is invalid."
    And the JSON node "errors.errors" should not exist
    And the JSON node "errors.fields.inventory.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.inventory.errors[0].message" should be equal to "This data type is not is data type that was expected."

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
    And the JSON node "message" should be equal to "Request input is invalid."
    And the JSON node "errors.errors" should not exist
    And the JSON node "errors.fields.inventory" should not exist
    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.name.errors[0].message" should be equal to "This value should not be blank."

  Scenario: Make a POST with an extra field
    When I send a POST request to "/api/v2/sandbox_widgets" with body:
    """
    {
      "inventory": 4,
      "extra": "this is not an expected input"
    }
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "status" should be equal to 400
    And the JSON node "code" should be equal to "invalid_input"
    And the JSON node "message" should be equal to "Request input is invalid."
    And the JSON node "errors.errors" should have 1 elements
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: extra"
    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.name.errors[0].message" should be equal to "This value should not be blank."
