Feature: /ticket_forms endpoint
  To ticket with layouts form
  As a developer
  I want to check creating/updating tickets via form with layouts

  Background:
    Given I install the api data set
    And my request is authenticated
    And the setting "core.use_product" is set to 1
    And the setting "core.use_ticket_priority" is set to 1
    And the setting "core.use_ticket_category" is set to 1
    And the setting "core.use_ticket_workflow" is set to 1

  @reinstall
  Scenario: I create a ticket
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    And I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "Sample Ticket",
  "department": 1,
  "message": {
    "message": "<p>my html message</p>",
    "format": "html"
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 5
    And the JSON node "data.subject" should be equal to "Sample Ticket"
    And the JSON node "data.department" should be equal to 1
    And the JSON node "data.product" should be equal to 0
    And the JSON node "data.priority" should be equal to 0
    And the JSON node "data.participants" should have 0 elements
    And the JSON node "data.followers" should have 0 elements

    When I send a GET request to "/api/v2/tickets/5/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].message" should contain "<p>my html message"
    And the JSON node "data[0].attachments" should have 0 elements

    When I send a GET request to "/api/v2/people/1"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Link Admin"
    And the JSON node "data.primary_email" should be equal to "admin@deskpro.dev"

  Scenario: I modify and retrieve a ticket
    When I send a PUT request to "/api/v2/ticket_forms/agent/5" with body:
    """
{
  "person": {
    "name": "Changed Name"
  },
  "subject": "Modified subject",
  "department": 2,
  "product": 2,
  "priority": 3,
  "category": 3,
  "workflow": 1,
  "cc": ["agent@deskpro.dev", "user@deskpro.dev"],
  "labels": ["ticket label 1", "ticket label 2"],
  "fields": {
    "1": "2",
    "5": "2016-02-09 17:28:00",
    "6": "inline text",
    "7": "textarea text",
    "8": ["10", "11"]
  },
  "user_fields": {
    "1": "2",
    "5": "2016-02-09 17:28:00",
    "6": "inline text",
    "7": "textarea text"
  },
  "organization_fields": {
    "1": "2",
    "5": "2016-02-09 17:28:00",
    "6": "inline text",
    "7": "textarea text"
  },
  "message": {
    "message": "my text message",
    "format": "text"
  },
  "attachments": [
    {"blob_auth": "AAAAAAAAAAAAAAAAAA"},
    {"blob_auth": "BBBBBBBBBBBBBBBBBB", "is_inline": true}
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/5"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 5
    And the JSON node "data.subject" should be equal to "Modified subject"
    And the JSON node "data.department" should be equal to 2
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.product" should be equal to 2
    And the JSON node "data.priority" should be equal to 3
    And the JSON node "data.category" should be equal to 3
    And the JSON node "data.workflow" should be equal to 1
    And the JSON node "data.participants" should have 1 element
    And the JSON node "data.participants[0]" should be equal to 3
    And the JSON node "data.followers" should have 1 element
    And the JSON node "data.followers[0]" should be equal to 2
    And the JSON node "data.labels" should have 2 elements
    And the JSON node "data.labels[0]" should be equal to "ticket label 1"
    And the JSON node "data.labels[1]" should be equal to "ticket label 2"
    And the JSON node "data.fields.1.value" should have 1 element
    And the JSON node "data.fields.1.value[0]" should be equal to 2
    And the JSON node "data.fields.1.detail.2.title" should be equal to "Small"
    And the JSON node "data.fields.5.value" should be equal to "2016-02-09T17:28:00+0000"
    And the JSON node "data.fields.6.value" should be equal to "inline text"
    And the JSON node "data.fields.7.value" should be equal to "textarea text"
    And the JSON node "data.fields.8.value" should have 2 element
    And the JSON node "data.fields.8.value[0]" should be equal to 10
    And the JSON node "data.fields.8.value[1]" should be equal to 11
    And the JSON node "data.fields.8.detail.10.title" should be equal to "Choice 2"
    And the JSON node "data.fields.8.detail.11.title" should be equal to "Choice 3"

    When I send a GET request to "/api/v2/tickets/5/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].message" should be equal to "my text message"
    And the JSON node "data[0].attachments" should have 2 elements
    And the JSON node "data[0].attachments[0]" should be equal to 1
    And the JSON node "data[0].attachments[1]" should be equal to 2

    When I send a GET request to "/api/v2/people/1"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Changed Name"
    And the JSON node "data.primary_email" should be equal to "admin@deskpro.dev"
    And the JSON node "data.fields.1.value" should have 1 element
    And the JSON node "data.fields.1.value[0]" should be equal to 2
    And the JSON node "data.fields.1.detail.2.title" should be equal to "Small"
    And the JSON node "data.fields.5.value" should be equal to "2016-02-09T17:28:00+0000"
    And the JSON node "data.fields.6.value" should be equal to "inline text"
    And the JSON node "data.fields.7.value" should be equal to "textarea text"

    When I send a GET request to "/api/v2/organizations/1"
    Then the response status code should be 200
    And the JSON node "data.fields.1.value" should have 1 element
    And the JSON node "data.fields.1.value[0]" should be equal to 2
    And the JSON node "data.fields.1.detail.2.title" should be equal to "Small"
    And the JSON node "data.fields.5.value" should be equal to "2016-02-09T17:28:00+0000"
    And the JSON node "data.fields.6.value" should be equal to "inline text"
    And the JSON node "data.fields.7.value" should be equal to "textarea text"

  Scenario: I modify custom checkbox group
    When I send a PUT request to "/api/v2/ticket_forms/agent/5" with body:
    """
{
  "fields": {
    "8": ["9", "11"]
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/5"
    Then the response status code should be 200
    And the JSON node "data.fields.8.value" should have 2 element
    And the JSON node "data.fields.8.value[0]" should be equal to 11
    And the JSON node "data.fields.8.value[1]" should be equal to 9
    And the JSON node "data.fields.8.detail.9.title" should be equal to "Choice 1"
    And the JSON node "data.fields.8.detail.11.title" should be equal to "Choice 3"

  Scenario: I modify custom text field in data serializer format
    When I send a PUT request to "/api/v2/ticket_forms/agent/5" with body:
    """
{
  "fields": {
    "6": {
      "value": "edited inline text"
    }
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/5"
    Then the response status code should be 200
    And the JSON node "data.fields.6.value" should be equal to "edited inline text"

  Scenario: I modify ticket person by id
    When I send a PUT request to "/api/v2/ticket_forms/agent/5" with body:
    """
{
  "person": 2
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/5"
    Then the response status code should be 200
    And the JSON node "data.person" should be equal to 2

  Scenario: I modify ticket person by email
    When I send a PUT request to "/api/v2/ticket_forms/agent/5" with body:
    """
{
  "person": "user@deskpro.dev"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/5"
    Then the response status code should be 200
    And the JSON node "data.person" should be equal to 3

    When I send a GET request to "/api/v2/tickets/5/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].person" should be equal to 3

  Scenario: I modify ticket person by creating a new person using name and email fields
    When I send a PUT request to "/api/v2/ticket_forms/agent/5" with body:
    """
{
  "person": {
    "email": "new-user@deskpro.dev",
    "name": "Some NewUser"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/5"
    Then the response status code should be 200
    And the JSON node "data.person" should be equal to 5

    When I send a GET request to "/api/v2/people/5"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Some NewUser"
    And the JSON node "data.primary_email" should be equal to "new-user@deskpro.dev"
    And the JSON node "data.emails[0]" should be equal to "new-user@deskpro.dev"

  Scenario: I change department and unset previous department fields
    When I send a PUT request to "/api/v2/ticket_forms/agent/5" with body:
    """
{
  "subject": "Sample Ticket",
  "department": 1,
  "message": {
    "message": "<p>my html message</p>",
    "format": "html"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/5"
    Then the response status code should be 200

    And the JSON node "data.id" should be equal to 5
    And the JSON node "data.subject" should be equal to "Sample Ticket"
    And the JSON node "data.department" should be equal to 1
    And the JSON node "data.fields" should have 0 elements

    When I send a GET request to "/api/v2/tickets/5/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].message" should contain "<p>my html message"

  Scenario: I set empty attachments
    When I send a PUT request to "/api/v2/ticket_forms/agent/5" with body:
    """
{
  "department": 2,
  "attachments": []
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/5/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].attachments" should have 0 elements
