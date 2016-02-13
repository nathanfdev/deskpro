Feature: /ticket_forms endpoint
  To ticket with layouts form
  As a developer
  I want to check validation errors

  Background:
    Given I install the api data set
    Given the setting "core.use_product" is set to 1
    Given the setting "core.use_ticket_priority" is set to 1
    And my request is authenticated

  # Subject
  Scenario: I try to create a ticket with empty subject
    When I send a POST request to "/api/v2/ticket_forms/agent"
#    And the JSON node "errors.fields.subject.errors[0].code" should be equal to "not_blank" @todo
    And the JSON node "errors.fields.subject.errors[0].message" should be equal to "This value should not be null."

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": ""
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.subject.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.subject.errors[0].message" should be equal to "This value should not be blank."

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "abc"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.subject.errors[0].code" should be equal to "wrong_length"
    # @todo
    And the JSON node "errors.fields.subject.errors[0].message" should be equal to "wrong_length"

  # Person
  Scenario: I try to create a ticket with person by unknown id
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": 10000
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "person_not_found"

  Scenario: I try to create a ticket with person by unknown email
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

  Scenario: I try to create a ticket with person with incorrect email
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
