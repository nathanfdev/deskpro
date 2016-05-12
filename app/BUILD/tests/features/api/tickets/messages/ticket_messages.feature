@tickets
Feature: /tickets/{id}/messages endpoint
  To CRUD DeskPRO ticket messages
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a ticket messages
    When I send a GET request to "/api/v2/tickets/1/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 0 element

    When I send a GET request to "/api/v2/tickets/1/messages/1"
    Then the response status code should be 404

  Scenario: I fail form validation
    When I send a POST request to "/api/v2/tickets/1/messages"
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors[0].message" should be equal to "This value should not be blank."

    When I send a GET request to "/api/v2/tickets/1/messages/1"
    Then the response status code should be 404

  Scenario: I add ticket messages
    Given I create an image blob with auth code "IMGAAAAAAAAAAAAAAA"
    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "My Message [attach:image:IMGAAAAAAAAAAAAAAA:image.jpg]",
      "attachments": [
        { "blob_auth": "IMGAAAAAAAAAAAAAAA" }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.ticket" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should be equal to 'My Message <a href="http://localhost/file.php/IMGAAAAAAAAAAAAAAA/image.jpg" target="_blank" class="dp-is-image dragout dp-embed-blob-a-IMGAAAAAAAAAAAAAAA" data-downloadurl="http://localhost/file.php/IMGAAAAAAAAAAAAAAA/image.jpg" data-blob-authid="IMGAAAAAAAAAAAAAAA"><img src="http://localhost/file.php/IMGAAAAAAAAAAAAAAA/image.jpg?s=350" title="image.jpg" class="dp-embed-blob-img-IMGAAAAAAAAAAAAAAA" /></a>'
    And the JSON node "data.attachments" should have 1 element
    And ticket with id=1 has "message_created" log

    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "<span>my html message</span>"
    }
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.ticket" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should be equal to "<span>my html message</span>"

    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "<span>my html message</span>",
      "format": "text"
    }
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.ticket" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should contain '&lt;span&gt;my html message&lt;'
    And the JSON node "data.attachments" should have 0 elements

    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "<span>my note</span>",
      "format": "html",
      "is_note": true
    }
    """
    Then the JSON node "data.id" should be equal to 4
    And the JSON node "data.ticket" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 1
    And the JSON node "data.message" should contain '<span>my note</span>'
    And the JSON node "data.attachments" should have 0 elements

  Scenario: I retrieve a ticket messages after adding
    When I send a GET request to "/api/v2/tickets/1/messages?include=person"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].message" should contain "my message"
    And the JSON node "data[1].message" should contain "my html message"
    And the JSON node "data[3].message" should contain "my note"
    And the JSON node "linked.person.1.id" should be equal to 1
    And the JSON node "linked.person.1.primary_email" should be equal to "admin@deskpro.dev"

    When I send a GET request to "/api/v2/tickets/1/messages/1"
    Then the response status code should be 200
    And the JSON node "data.message" should contain "my message"

    When I send a GET request to "/api/v2/tickets/1/messages/4"
    Then the response status code should be 200
    And the JSON node "data.message" should contain "my note"

  Scenario: I create a message with attachments
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    Given I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "<span>my message with attachments</span>",
      "format": "html",
      "attachments": [
        {"blob_auth": "AAAAAAAAAAAAAAAAAA"},
        {"blob_auth": "BBBBBBBBBBBBBBBBBB", "is_inline": true}
      ]
    }
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 5
    And the JSON node "data.attachments" should have 2 elements
    And the JSON node "data.attachments[0]" should be equal to 2
    And the JSON node "data.attachments[1]" should be equal to 3

  Scenario: I'm checking last created message with sideloading
    When I send a GET request to "/api/v2/tickets/1/messages/5?include=ticket_attachment"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 5

    And the JSON node "linked.ticket_attachment.2.id" should be equal to 2
    And the JSON node "linked.ticket_attachment.2.blob.blob_id" should be equal to 6
    And the JSON node "linked.ticket_attachment.2.blob.content_type" should be equal to "text/plain"
    And the JSON node "linked.ticket_attachment.3.id" should be equal to 3
    And the JSON node "linked.ticket_attachment.3.blob.blob_id" should be equal to 7
    And the JSON node "linked.ticket_attachment.3.blob.content_type" should be equal to "text/plain"

  Scenario: I create a text message with is_note = false
    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "<p style=\" \"><font face=\".SF UI Text\"  style=\" font-size:14px; \" >Test Note<\/font><\/p>",
      "format": "html",
      "is_note": false
    }
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 6
    And the JSON node "data.message" should contain "Test Note"
    And the JSON node "data.is_agent_note" should be equal to 0

  Scenario: I create a text message with is_note = 0
    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "<p style=\" \"><font face=\".SF UI Text\"  style=\" font-size:14px; \" >Test Note<\/font><\/p>",
      "format": "html",
      "is_note": 0
    }
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 7
    And the JSON node "data.message" should contain "Test Note"
    And the JSON node "data.is_agent_note" should be equal to 0

  Scenario: I create a note with is_note = 1
    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "<p style=\" \"><font face=\".SF UI Text\"  style=\" font-size:14px; \" >Test Note<\/font><\/p>",
      "format": "html",
      "is_note": 1
    }
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 8
    And the JSON node "data.message" should contain "Test Note"
    And the JSON node "data.is_agent_note" should be equal to 1

  Scenario: I reset ticket message attachments
    When I send a PUT request to "/api/v2/tickets/1/messages/5" with body:
      """
    {
      "message": "<span>my edited message without attachments</span>",
      "format": "text",
      "attachments": []
    }
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/1/messages/5"
    Then the response status code should be 200
    And the JSON node "data.message" should contain "&lt;span&gt;my edited message without attachments&lt;"
    And the JSON node "data.attachments" should have 0 elements

  Scenario: I delete message
    Given I reset ticket with id=1 logs
    When I send a DELETE request to "/api/v2/tickets/1/messages/5"
    Then the response status code should be 200
    And ticket with id=1 has "message_removed" log

    When I send a GET request to "/api/v2/tickets/1/messages/5"
    Then the response status code should be 404

  Scenario: I check sideloading of form errors
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 2
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 5

  Scenario: I add ticket messages
    When I send a POST request to "/api/v2/tickets/5/messages?with_ticket_validation=1" with body:
    """
{
  "message": "my message"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.ticket.fields.fields.fields.fields_6.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.ticket.fields.fields.fields.fields_6.errors[0].message" should contain "This value should not be blank."
    And the JSON node "errors.fields.ticket.fields.organization_fields.fields.organization_fields_6.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.ticket.fields.organization_fields.fields.organization_fields_6.errors[0].message" should contain "This value should not be blank."
    And the JSON node "errors.fields.ticket.fields.user_fields.fields.user_fields_6.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.ticket.fields.user_fields.fields.user_fields_6.errors[0].message" should contain "This value should not be blank."
