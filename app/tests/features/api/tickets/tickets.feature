Feature: /tickets endpoint
  To CRUD DeskPRO tickets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a ticket
    When I send a GET request to "/api/v2/tickets/1"
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "Test"

  @basic
  Scenario: I retrieve list of tickets
    When I send a GET request to "/api/v2/tickets?sort=id&order=desc"
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].subject" should be equal to "Ticket #3"
    And the JSON node "data[1].subject" should be equal to "Ticket #2"

  Scenario: I try to create a ticket providing empty data
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.subject" should exist

  @basic
  Scenario: I create a ticket
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    And I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "department": 1,
  "message": {
    "message_html": "<p>my html message</p>",
    "message_format": "html"
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

    When I send a GET request to "/api/v2/tickets/5/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].message" should contain "<p>my html message"

    # Department with id = 1 has default layout without attachments field
    And the JSON node "data[0].attachments" should have 0 elements

  @basic
  Scenario: I modify and retrieve a ticket
    When I send a PUT request to "/api/v2/tickets/5" with body:
    """
{
  "subject": "Modified subject",
  "department": 2,
  "message": {
    "message_text": "my text message",
    "message_format": "text"
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

    When I send a GET request to "/api/v2/tickets/5/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].message" should be equal to "my text message"
    And the JSON node "data[0].attachments" should have 2 elements
    And the JSON node "data[0].attachments[0]" should be equal to 1
    And the JSON node "data[0].attachments[1]" should be equal to 2

  @basic
  Scenario: I delete a ticket
    When I send a DELETE request to "/api/v2/tickets/5"
    Then the response should be in JSON
    And the response status code should be 200

  @basic
  Scenario: I try to get deleted ticket
    When I send a GET request to "/api/v2/tickets/5"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "deleted"

  Scenario: I try to get not existing ticket
    When I send a GET request to "/api/v2/tickets/40404"
    Then the response status code should be 404

  Scenario: I try to modify not existing ticket
    When I send a PUT request to "/api/v2/tickets/40404" with body:
    """
{
  "subject": "Modified subject"
}
    """
    Then the response status code should be 404
