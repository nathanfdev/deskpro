@new
Feature: /ticket_forms validation
  I want to check subject validation

  Background:
    Given I'm authenticated as admin

  Scenario: I try to create a ticket with no subject property in request
    When I send a POST request to "/api/v2/ticket_forms/agent"
    Then the response status code should be 400
    And the JSON node "errors.fields.subject.errors" should have 1 element
    And the JSON node "errors.fields.subject.errors[0].code" should be equal to "required"

  Scenario: I try to create a ticket with empty subject (and too short)
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": ""
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.subject.errors" should have 1 element
    And the JSON node "errors.fields.subject.errors[0].code" should be equal to "required"

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "abc"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.subject.errors" should have 1 element
    And the JSON node "errors.fields.subject.errors[0].code" should be equal to "length_too_short"
    And the JSON node "errors.fields.subject.errors[0].message" should contain "5"

  Scenario: I sent not valid data type in subject:
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": {
    "title": "subject"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.subject.errors" should have 1 element
    And the JSON node "errors.fields.subject.errors[0].code" should be equal to "invalid_data_type"
