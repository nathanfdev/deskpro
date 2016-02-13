Feature: /ticket_forms endpoint
  To ticket with layouts form
  As a developer
  I want to check validation errors

  Background:
    Given I install the api data set
    Given the setting "core.use_product" is set to 1
    Given the setting "core.use_ticket_priority" is set to 1
    And my request is authenticated


  # Person field
  Scenario: I create a ticket with person by unknown id
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": 10000
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "person_not_found"

  Scenario: I create a ticket with person by unknown email
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": "unknown-email@deskpro.dev"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.fields.name.errors[0].code" should be equal to "not_blank"

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": {
    "email": "unknown-email@deskpro.dev"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.fields.name.errors[0].code" should be equal to "not_blank"

  Scenario: I create a ticket with person with incorrect email
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
    And the JSON node "errors.fields.person.fields.email.errors[0].code" should be equal to "invalid_email"

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": "incorrect - emaildeskpro.dev"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.fields.email.errors[0].code" should be equal to "invalid_email"
