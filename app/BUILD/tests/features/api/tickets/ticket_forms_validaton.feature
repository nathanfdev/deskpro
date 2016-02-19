Feature: /ticket_forms endpoint
  To ticket with layouts form
  As a developer
  I want to check validation errors

  Background:
    Given I install the api data set
    And my request is authenticated
    And the setting "core.use_product" is set to 1
    And the setting "core.use_ticket_priority" is set to 1
    And the setting "core.use_ticket_category" is set to 1
    And the setting "core.use_ticket_workflow" is set to 1

  Scenario: I try to create a ticket with empty subject (empty request)
    When I send a POST request to "/api/v2/ticket_forms/agent"
    Then the response status code should be 400
    And the JSON node "errors.fields.message.fields.message.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.message.fields.message.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I try to create a ticket with empty subject (too short)
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "abc"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.subject.errors[0].code" should be equal to "wrong_length"
    And the JSON node "errors.fields.subject.errors[0].message" should be equal to "The value must be at least 5 characters in length."

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
    And the JSON node "errors.fields.subject.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.subject.errors[0].message" should be equal to "This data type is not is data type that was expected."

  Scenario: I try to create a ticket with person by unknown id
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": 10000
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "person_not_found"
    And the JSON node "errors.fields.person.errors[0].message" should contain "Person with identifier"
    And the JSON node "errors.fields.person.errors[0].message" should contain "10000"

  Scenario: I try to create a ticket with person by unknown email (inline)
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": "unknown-email@deskpro.dev"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.person.fields.name.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I try to create a ticket with person by unknown email (email key)
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": {
    "email": "unknown-email@deskpro.dev"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.person.fields.name.errors[0].message" should be equal to "This value should not be blank."

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
    And the JSON node "errors.fields.person.fields.email.errors[0].code" should be equal to "invalid_email"
    And the JSON node "errors.fields.person.fields.email.errors[0].message" should contain "is not a valid email address."

  Scenario: I try to create a ticket with person with incorrect email (inline)
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": "incorrect - emaildeskpro.dev"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.fields.email.errors[0].code" should be equal to "invalid_email"
    And the JSON node "errors.fields.person.fields.email.errors[0].message" should contain "is not a valid email address."

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
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.fields.person.errors[0].message" should contain "Unexpected field names: extra_field"

  Scenario: I sent unknown department choice:
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 404
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.department.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.department.errors[0].message" should contain "One or more of the given values is invalid."

  Scenario: I sent not valid data type in choice:
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": {
    "id": 1
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.department.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.department.errors[0].message" should be equal to "This data type is not is data type that was expected."

  Scenario Outline: I sent unknown choice:
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "<field>": 404
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.<field>.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.<field>.errors[0].message" should contain "One or more of the given values is invalid."

    Examples:
      | field    |
      | product  |
      | category |
      | workflow |
      | priority |

  Scenario Outline: I sent not valid data type in choice:
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "<field>": {
    "id": 1
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.<field>.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.<field>.errors[0].message" should be equal to "This data type is not is data type that was expected."

    Examples:
      | field    |
      | product  |
      | category |
      | workflow |
      | priority |

  Scenario: I sent not valid data in labels:
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "labels": [
    {"id": 1}
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.labels.fields.labels_0.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.labels.fields.labels_0.errors[0].message" should be equal to "This data type is not is data type that was expected."

  Scenario: I sent not valid cc email data
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "cc": [
    {"id": 1}
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.cc.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.cc.errors[0].message" should be equal to "This data type is not is data type that was expected."

  Scenario: I sent not valid cc email
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "cc": ["not_valid_email"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.cc.errors[0].code" should be equal to "invalid_email"
    And the JSON node "errors.fields.cc.errors[0].message" should contain "is not a valid email address."
    And the JSON node "errors.fields.cc.errors[0].message" should contain "not_valid_email"
