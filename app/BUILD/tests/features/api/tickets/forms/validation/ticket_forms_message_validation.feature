@new
Feature: /ticket_forms validation
  I want to check message validation

  Background:
    Given I'm authenticated as admin

  Scenario: I try to create a ticket with no subject property in request
    When I send a POST request to "/api/v2/ticket_forms/agent"
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors" should not exist
    And the JSON node "errors.fields.message.fields" should have 1 element
    And the JSON node "errors.fields.message.fields.message.errors" should have 1 element
    And the JSON node "errors.fields.message.fields.message.errors[0].code" should be equal to "required"

  Scenario: I sent not valid message format
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "message": "abc"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors" should have 1 element
    And the JSON node "errors.fields.message.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.message.fields" should not exist

  Scenario: I sent message with extra fields
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "message": {
    "message": "",
    "extra_field": ""
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors" should have 1 element
    And the JSON node "errors.fields.message.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.fields.message.errors[0].message" should contain "extra_field"
    And the JSON node "errors.fields.message.fields" should have 1 element
    And the JSON node "errors.fields.message.fields.message.errors" should have 1 element
    And the JSON node "errors.fields.message.fields.message.errors[0].code" should be equal to "required"

  Scenario: I sent empty message
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "message": {
    "message": ""
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors" should not exist
    And the JSON node "errors.fields.message.fields" should have 1 element
    And the JSON node "errors.fields.message.fields.message.errors" should have 1 element
    And the JSON node "errors.fields.message.fields.message.errors[0].code" should be equal to "required"

  Scenario: I sent not valid message
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "message": {
    "message": {
      "content": "message"
    }
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors" should not exist
    And the JSON node "errors.fields.message.fields" should have 1 element
    And the JSON node "errors.fields.message.fields.message.errors" should have 1 element
    And the JSON node "errors.fields.message.fields.message.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I sent wrong message format
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "message": {
    "format": "unknown"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors" should not exist
    And the JSON node "errors.fields.message.fields" should have 2 elements
    And the JSON node "errors.fields.message.fields.format.errors" should have 1 element
    And the JSON node "errors.fields.message.fields.format.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.message.fields.message.errors" should have 1 element
    And the JSON node "errors.fields.message.fields.message.errors[0].code" should be equal to "required"

  Scenario: I sent too short message
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "message": {
    "message": "abc"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors" should not exist
    And the JSON node "errors.fields.message.fields" should have 1 element
    And the JSON node "errors.fields.message.fields.message.errors" should have 1 element
    And the JSON node "errors.fields.message.fields.message.errors[0].code" should be equal to "length_too_short"
    And the JSON node "errors.fields.message.fields.message.errors[0].message" should contain "10"
