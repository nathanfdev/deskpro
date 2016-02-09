Feature: /tickets endpoint
  To CRUD DeskPRO tickets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I create a ticket
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    And I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    When I send a POST request to "/api/v2/ticket_forms" with body:
    """
{
  "subject": "Sample Ticket",
  "department": 1,
  "product": 2,
  "priority": 3,
  "cc": "agent@deskpro.dev, user@deskpro.dev",
  "message": {
    "message": "<p>my html message</p>",
    "format": "html"
  },
  "attachments": [
    {"blob_auth": "AAAAAAAAAAAAAAAAAA"},
    {"blob_auth": "BBBBBBBBBBBBBBBBBB", "is_inline": true}
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 5
    And the JSON node "data.subject" should be equal to "Sample Ticket"
    And the JSON node "data.department" should be equal to 1

    # Layout has no product field and "core.use_product" = 0
    And the JSON node "data.product" should be equal to 0

    # Layout has no priority field and "core.use_ticket_priority" = 0
    And the JSON node "data.priority" should be equal to 0

    # Layout has not cc field
    And the JSON node "data.participants" should have 0 elements
    And the JSON node "data.followers" should have 0 elements

    When I send a GET request to "/api/v2/tickets/5/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].message" should contain "<p>my html message"

    # Department with id = 1 has default layout without attachments field
    And the JSON node "data[0].attachments" should have 0 elements

  Scenario: I modify and retrieve a ticket
    Given the setting "core.use_product" is set to 1
    Given the setting "core.use_ticket_priority" is set to 1
    When I send a PUT request to "/api/v2/ticket_forms/5" with body:
    """
{
  "person": {
    "user_name": "Changed Name"
  },
  "subject": "Modified subject",
  "department": 2,
  "product": 2,
  "priority": 3,
  "cc": "agent@deskpro.dev, user@deskpro.dev",
  "ticket_field_1": {
    "data": "2"
  },
  "ticket_field_5": {
    "input": {
      "date": {
        "year": "2016",
        "month": "2",
        "day": "9"
      },
      "time": {
        "hour": "17",
        "minute": "28"
      }
    }
  },
  "ticket_field_6": {
    "input": "inline text"
  },
  "ticket_field_7": {
    "input": "textarea text"
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
    And I send a GET request to "/api/v2/tickets/5"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 5
    And the JSON node "data.subject" should be equal to "Modified subject"
    And the JSON node "data.department" should be equal to 2
    And the JSON node "data.product" should be equal to 2
    And the JSON node "data.priority" should be equal to 3
    And the JSON node "data.participants" should have 1 element
    And the JSON node "data.participants[0]" should be equal to 3
    And the JSON node "data.followers" should have 1 element
    And the JSON node "data.followers[0]" should be equal to 2
    And the JSON node "data.fields.1.value" should have 1 element
    And the JSON node "data.fields.1.value[0]" should be equal to 2
    And the JSON node "data.fields.1.detail.1.title" should be equal to "Desired Sizes"
    And the JSON node "data.fields.5.value" should be equal to "2016-02-09T17:28:00+0000"
    And the JSON node "data.fields.6.value" should be equal to "inline text"
    And the JSON node "data.fields.7.value" should be equal to "textarea text"

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
