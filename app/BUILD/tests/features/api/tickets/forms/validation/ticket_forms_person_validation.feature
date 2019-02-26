@new
Feature: /ticket_forms validation
  I want to check person validation

  Background:
    Given I'm authenticated as admin

  Scenario: I try to create a ticket with person by unknown id
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": -1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors" should have 2 elements
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "person_not_found"
    And the JSON node "errors.fields.person.errors[0].message" should contain "-1"
    And the JSON node "errors.fields.person.errors[1].code" should be equal to "required"

  Scenario: I try to create a ticket with person with incorrect email (email key)
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": {
    "email": "incorrect - emaildeskpro.dev",
    "name": "Some NewUser"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors" should not exist
    And the JSON node "errors.fields.person.fields" should have 1 element
    And the JSON node "errors.fields.person.fields.email.errors" should have 1 element
    And the JSON node "errors.fields.person.fields.email.errors[0].code" should be equal to "invalid_email"

  Scenario: I try to create a ticket with person with incorrect email (inline)
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": "incorrect - emaildeskpro.dev"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors" should not exist
    And the JSON node "errors.fields.person.fields" should have 1 elements
    And the JSON node "errors.fields.person.fields.email.errors" should have 1 element
    And the JSON node "errors.fields.person.fields.email.errors[0].code" should be equal to "invalid_email"

  Scenario: I sent extra data in person field:
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": {
    "extra_field": "some value"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors" should have 1 element
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.fields.person.errors[0].message" should contain "extra_field"
    And the JSON node "errors.fields.person.fields" should not exist
