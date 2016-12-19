Feature: /tickets/{id}/messages endpoint
  To CRUD DeskPRO ticket messages
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve a ticket messages
    Given I create a Ticket and reference it as ticket
    When I send a GET request to "/api/v2/tickets/{ticket}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 0 element

    When I send a GET request to "/api/v2/tickets/{ticket}/messages/1"
    Then the response status code should be 404

  Scenario: I fail form validation
    When I send a POST request to "/api/v2/tickets/{ticket}/messages"
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors[0].message" should be equal to "This value should not be blank."

    When I send a GET request to "/api/v2/tickets/{ticket}/messages/1"
    Then the response status code should be 404

  Scenario: I add ticket messages
    Given I create an image blob with auth code "IMGAAAAAAAAAAAAAAA"
    When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "My Message [attach:image:IMGAAAAAAAAAAAAAAA:image.jpg]",
  "attachments": [
    { "blob_auth": "IMGAAAAAAAAAAAAAAA" }
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should contain 'My Message'
    And the JSON node "data.message" should contain 'IMGAAAAAAAAAAAAAAA/image.jpg'
    And the JSON node "data.message" should contain '<img'
    And the JSON node "data.attachments" should have 1 element

    When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "<span>my html message</span>"
}
    """
    Then the response status code should be 201
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should be equal to "<span>my html message</span>"

    When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "<span>my html message</span>",
  "format": "text"
}
    """
    Then the response status code should be 201
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should contain '&lt;span&gt;my html message&lt;'
    And the JSON node "data.attachments" should have 0 elements

    When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "<span>my note</span>",
  "format": "html",
  "is_note": true
}
    """
    Then the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 1
    And the JSON node "data.message" should contain '<span>my note</span>'
    And the JSON node "data.attachments" should have 0 elements

  Scenario: I retrieve a ticket messages after adding
    When I send a GET request to "/api/v2/tickets/{ticket}/messages?include=person"
    Then the response status code should be 200
    And the JSON node "data" should have 4 element
    And the JSON node "data[0].message" should contain "my message"
    And the JSON node "data[1].message" should contain "my html message"
    And the JSON node "data[3].message" should contain "my note"
    And the JSON node "linked.person.1.id" should be equal to 1
    And the JSON node "linked.person.1.primary_email" should be equal to "admin@deskpro.dev"

    When I send a GET request to "/api/v2/tickets/{ticket}/messages/{lastCreatedId}"
    Then the response status code should be 200
    And the JSON node "data.message" should contain "my note"

  Scenario: I create a message with attachments
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    Given I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
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
    And the JSON node "data.attachments" should have 2 elements

  Scenario: I'm checking last created message with sideloading
    When I send a GET request to "/api/v2/tickets/{ticket}/messages/{lastCreatedId}?include=ticket_attachment"
    Then the response status code should be 200
    And the JSON node "linked.ticket_attachment" should have 2 elements

  Scenario: I reset ticket message attachments
    When I send a PUT request to "/api/v2/tickets/{ticket}/messages/{lastCreatedId}" with body:
    """
{
  "message": "<span>my edited message without attachments</span>",
  "format": "text",
  "attachments": []
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{ticket}/messages/{lastCreatedId}"
    Then the response status code should be 200
    And the JSON node "data.message" should contain "&lt;span&gt;my edited message without attachments&lt;"
    And the JSON node "data.attachments" should have 0 elements

  Scenario: I create a text message with is_note = false
    When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "<p style=\" \"><font face=\".SF UI Text\"  style=\" font-size:14px; \" >Test Note 1<\/font><\/p>",
  "format": "html",
  "is_note": false
}
    """
    Then the response status code should be 201
    And the JSON node "data.message" should contain "Test Note 1"
    And the JSON node "data.is_agent_note" should be equal to 0

  Scenario: I create a text message with is_note = 0
    When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "<p style=\" \"><font face=\".SF UI Text\"  style=\" font-size:14px; \" >Test Note 2<\/font><\/p>",
  "format": "html",
  "is_note": 0
}
    """
    Then the response status code should be 201
    And the JSON node "data.message" should contain "Test Note 2"
    And the JSON node "data.is_agent_note" should be equal to 0

  Scenario: I create a note with is_note = 1
    When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "<p style=\" \"><font face=\".SF UI Text\"  style=\" font-size:14px; \" >Test Note 3<\/font><\/p>",
  "format": "html",
  "is_note": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.message" should contain "Test Note 3"
    And the JSON node "data.is_agent_note" should be equal to 1

  Scenario: I delete message
    When I send a DELETE request to "/api/v2/tickets/{ticket}/messages/{lastCreatedId}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/{ticket}/messages/{lastCreatedId}"
    Then the response status code should be 404

  Scenario Outline: I check ios purify
    Given I add "x-deskpro-api-clienttype" header equal to "<client_type>"
    When I send a POST request to "/api/v2/tickets/{ticket}/messages" with body:
    """
{
  "message": "Test\n\nTest\n\nTest\n<client_type><p class=\"dp-signature-start\">Regards,\n\nAdmin Admin",
  "format": "html"
}
    """
    Then the response status code should be 201
    And the JSON node "data.message" should contain "Test<br><br>"
    And the JSON node "data.message" should contain "</p>"

    Examples:
      | client_type   |
      | ios           |
      | iOS (v1.0.92) |
