@tickets
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

  @reinstall
  Scenario: I try to create a ticket with empty subject (empty request)
    When I send a POST request to "/api/v2/ticket_forms/agent"
    Then the response status code should be 400
    And the JSON node "errors.fields.message.fields.message.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.message.fields.message.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I sent not valid message format
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "message": "abc"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.message.errors[0].message" should be equal to "This data type is not is data type that was expected."

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
    And the JSON node "errors.fields.message.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.fields.message.errors[0].message" should be equal to "Unexpected field names: extra_field"

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
    And the JSON node "errors.fields.message.fields.message.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.message.fields.message.errors[0].message" should be equal to "This value should not be blank."

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
    And the JSON node "errors.fields.message.fields.message.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.message.fields.message.errors[0].message" should be equal to "This data type is not is data type that was expected."

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
    And the JSON node "errors.fields.message.fields.format.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.message.fields.format.errors[0].message" should be equal to "One or more of the given values is invalid."

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
    And the JSON node "errors.fields.message.fields.message.errors[0].code" should be equal to "length_too_short"
    And the JSON node "errors.fields.message.fields.message.errors[0].message" should be equal to "This value is too short. It should have 10 characters or more."

  Scenario: I try to create a ticket with empty subject (empty subject)
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": ""
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.subject.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.subject.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I try to create a ticket with empty subject (too short)
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "abc"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.subject.errors[0].code" should be equal to "length_too_short"
    And the JSON node "errors.fields.subject.errors[0].message" should be equal to "This value is too short. It should have 5 characters or more."

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

  Scenario: I sent empty label:
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "labels": ["", "label1"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.labels.fields.labels_0.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.labels.fields.labels_0.errors[0].message" should be equal to "This value should not be blank."

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
    And the JSON node "errors.fields.cc.errors[0].code" should be equal to "person_not_found"
    And the JSON node "errors.fields.cc.errors[0].message" should contain "Person with identifier"
    And the JSON node "errors.fields.cc.errors[0].message" should contain "not_valid_email"

  Scenario: I confused participant fields
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "cc": ["agent@deskpro.dev"],
  "followers": ["user@deskpro.dev"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.cc.errors[0].code" should be equal to "person_not_user"
    And the JSON node "errors.fields.cc.errors[0].message" should contain "Person with identifier"
    And the JSON node "errors.fields.cc.errors[0].message" should contain "agent@deskpro.dev"
    And the JSON node "errors.fields.cc.errors[0].message" should contain "is not user"
    And the JSON node "errors.fields.followers.errors[0].code" should be equal to "person_not_agent"
    And the JSON node "errors.fields.followers.errors[0].message" should contain "Person with identifier"
    And the JSON node "errors.fields.followers.errors[0].message" should contain "user@deskpro.dev"
    And the JSON node "errors.fields.followers.errors[0].message" should contain "is not agent"

  Scenario: I sent not valid data for custom data
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "fields": "some data"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.fields.errors[0].message" should be equal to "This data type is not is data type that was expected."

  Scenario: I sent not valid data for custom data single choice
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "fields": {
    "1": "not_vaild_choice"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_1.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.fields.fields.fields_1.errors[0].message" should contain "One or more of the given values is invalid."

  Scenario: I sent not valid datetime string
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "fields": {
    "5": "not_vaild_datetime"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_5.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.fields.fields.fields_5.errors[0].message" should contain "This data type is not is data type that was expected."

  Scenario: I sent not valid date string
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "fields": {
    "12": "not_vaild_datetime"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_12.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.fields.fields.fields_12.errors[0].message" should contain "This data type is not is data type that was expected."

  Scenario: I sent not valid custom data text
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "fields": {
    "6": {
      "text": "some text"
    }
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_6.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.fields.fields.fields_6.errors[0].message" should contain "This value should not be blank."
    And the JSON node "errors.fields.fields.fields.fields_6.errors[1].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.fields.fields.fields_6.errors[1].message" should contain "This data type is not is data type that was expected."

  Scenario: I sent not valid custom data textarea
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "fields": {
    "7": {
      "text": "some text"
    }
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_7.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.fields.fields.fields_7.errors[0].message" should contain "This data type is not is data type that was expected."

  Scenario: I sent not valid attachments data
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "attachments": "AAAAAAAAAAAAAAAAAA"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.attachments.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.attachments.errors[0].message" should contain "This data type is not is data type that was expected."

  Scenario: I sent not valid attachment
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "attachments": [
    "AAAAAAAAAAAAAAAAAA"
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.attachments.fields.attachments_0.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.attachments.fields.attachments_0.errors[0].message" should contain "This data type is not is data type that was expected."

  Scenario: I sent attachment with empty blob auth code
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "attachments": [
    {"blob_auth": ""}
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.attachments.fields.attachments_0.fields.blob_auth.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.attachments.fields.attachments_0.fields.blob_auth.errors[0].message" should contain "This value should not be blank."

  Scenario: I modify ticket using user layout (doesn't have product and priority fields)
    When I send a POST request to "/api/v2/ticket_forms/user" with body:
    """
{
  "department": 2,
  "product": 3,
  "priority": 2
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should contain "Unexpected field names:"
    And the JSON node "errors.errors[0].message" should contain "product"
    And the JSON node "errors.errors[0].message" should contain "priority"

  Scenario: I check custom field validation
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2,
  "fields": {
    "6": "short"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_6.errors[0].code" should be equal to "length_too_short"
    And the JSON node "errors.fields.fields.fields.fields_6.errors[0].message" should contain "This value is too short. It should have 10 characters or more."

    When I send a PUT request to "/api/v2/ticket_forms/agent/1" with body:
    """
{
  "department": 2,
  "fields": {
    "6": "short"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_6.errors[0].code" should be equal to "length_too_short"
    And the JSON node "errors.fields.fields.fields.fields_6.errors[0].message" should contain "This value is too short. It should have 10 characters or more."

    When I send a PUT request to "/api/v2/ticket_forms/agent/1" with body:
    """
{
  "department": 2,
  "fields": {
    "6": ""
  }
}
    """
    And the JSON node "errors.fields.fields.fields.fields_6.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.fields.fields.fields_6.errors[0].message" should contain "This value should not be blank."
