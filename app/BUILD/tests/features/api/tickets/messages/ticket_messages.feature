@new
Feature: /tickets/{id}/messages endpoint
  To CRUD DeskPRO ticket messages
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And there are no Blob records in the DB
    And no TicketMessage records exist
    And only the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |

  Scenario: I retrieve a ticket messages
    Given I create a Ticket and reference it as ticket
    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 0 element

    When I send a GET request to "/api/v2/tickets/{t1}/messages/0"
    Then the response status code should be 404

  Scenario: I retrieve a ticket messages by ticket ref
    When I send a GET request to "/api/v2/tickets/{t1:ref}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 0 element

  Scenario: I fail form validation
    When I send a POST request to "/api/v2/tickets/{t1}/messages"
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors[0].code" should be equal to "required"

  Scenario: I add ticket message w/ inline attachment
    Given I create an image blob with auth code "IMGAAAAAAAAAAAAAAA"
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "My Message [attach:image:IMGAAAAAAAAAAAAAAA:image.jpg]",
  "attachments": [
    { "blob_auth": "IMGAAAAAAAAAAAAAAA" }
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should contain 'My Message'
    And the JSON node "data.message" should contain 'IMGAAAAAAAAAAAAAAA/image.jpg'
    And the JSON node "data.message" should contain '<img'
    And the JSON node "data.attachments" should have 1 element

  Scenario: I add html ticket message implicit way
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "<span>my html message</span>"
}
    """
    Then the response status code should be 201
    And the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should be equal to "<span>my html message</span>"

  Scenario: I add html ticket message explicit way
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "<span>my note</span>",
  "format": "html",
  "is_note": true
}
    """
    Then the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.is_agent_note" should be equal to 1
    And the JSON node "data.message" should contain '<span>my note</span>'
    And the JSON node "data.attachments" should have 0 elements

  Scenario: I add text ticket message
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "<span>my html message</span>",
  "format": "text"
}
    """
    Then the response status code should be 201
    And the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should contain '&lt;span&gt;my html message&lt;'
    And the JSON node "data.attachments" should have 0 elements

  Scenario: I retrieve a ticket messages
    Given only the following TicketMessage records exist:
      | #  | Person  | Ticket | Message         |
      | m1 | {admin} | {t1}   | my message      |
      | m2 | {admin} | {t1}   | my html message |
      | m3 | {admin} | {t1}   | my note         |

    When I send a GET request to "/api/v2/tickets/{t1}/messages?include=person"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].message" should contain "my message"
    And the JSON node "data[1].message" should contain "my html message"
    And the JSON node "data[2].message" should contain "my note"
    And the JSON node "linked.person.{admin}.id" should be equal to "{admin}"
    And the JSON node "linked.person.{admin}.primary_email" should be equal to "admin@deskpro.dev"

    When I send a GET request to "/api/v2/tickets/{t1}/messages/{m3}"
    Then the response status code should be 200
    And the JSON node "data.message" should contain "my note"

  Scenario: I create a message with attachments
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    And I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
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

    When I send a GET request to "/api/v2/tickets/{t1}/messages/{lastCreatedId}?include=ticket_attachment"
    Then the response status code should be 200
    And the JSON node "linked.ticket_attachment" should have 2 elements

  Scenario: I reset ticket message attachments
    Given only the following TicketMessage records exist:
      | #  | Person  | Ticket | Message         |
      | m1 | {admin} | {t1}   | my message      |
    And only the following Blob records exist:
      | #  | Filename | Blob Hash          |
      | b1 | file.jpg | AAAAAAAAAAAAAAAAAA |
    And only the following TicketAttachment records exist:
      | #  | Blob | Person  | Ticket | Message |
      | a1 | {b1} | {admin} | {t1}   | {m1}    |

    When I send a PUT request to "/api/v2/tickets/{t1}/messages/{m1}" with body:
    """
{
  "message": "<span>my edited message without attachments</span>",
  "format": "text",
  "attachments": []
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/messages/{m1}"
    Then the response status code should be 200
    And the JSON node "data.message" should contain "&lt;span&gt;my edited message without attachments&lt;"
    And the JSON node "data.attachments" should have 0 elements

  Scenario: I create a text message with is_note = false
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
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
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
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
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
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
    Given only the following TicketMessage records exist:
      | #  | Person  | Ticket | Message    |
      | m1 | {admin} | {t1}   | My message |

    When I send a DELETE request to "/api/v2/tickets/{t1}/messages/{m1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/{t1}/messages/{m1}"
    Then the response status code should be 404

  Scenario Outline: I check ios purify
    Given I add "x-deskpro-api-clienttype" header equal to "<client_type>"
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
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

  Scenario: I reply to a ticket and change its status
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "status": "awaiting_user"
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.status" should be equal to the string "awaiting_user"

  Scenario: If I don't send status then ticket status should not be changed
    Given only the following Ticket records exist:
      | #  | Subject  | Status   |
      | t1 | Ticket 1 | resolved |

    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message"
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.status" should be equal to the string "resolved"

  Scenario: I retrieve a ticket message attachments empty list
    Given only the following TicketMessage records exist:
      | #  | Person  | Ticket | Message         |
      | m1 | {admin} | {t1}   | my message      |
    When I send a GET request to "/api/v2/tickets/{t1}/messages/{m1}/attachments"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I retrieve a ticket message attachments list for non existing message
    When I send a GET request to "/api/v2/tickets/{t1}/messages/0/attachments"
    Then the response status code should be 404

  Scenario: I retrieve a ticket message attachments
    Given only the following TicketMessage records exist:
      | #  | Person  | Ticket | Message         |
      | m1 | {admin} | {t1}   | my message      |
      | m2 | {admin} | {t1}   | noisy message   |
    And I create blob with auth code "m1blob_AAAAAAAAAAA"
    And I create blob with auth code "m1blob_BBBBBBBBBBB"
    And I create blob with auth code "m2blob_CCCCCCCCCCC"
    And only the following TicketAttachment records exist:
      | Ticket | Person  | Message | Blob                      |
      | {t1}   | {admin} | {m1}    | {blob_m1blob_AAAAAAAAAAA} |
      | {t1}   | {admin} | {m1}    | {blob_m1blob_BBBBBBBBBBB} |
      | {t1}   | {admin} | {m2}    | {blob_m2blob_CCCCCCCCCCC} |
    When I send a GET request to "/api/v2/tickets/{t1}/messages/{m1}/attachments"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].blob.blob_auth" should contain "m1blob_AAAAAAAAAAA"
    And the JSON node "data[1].blob.blob_auth" should contain "m1blob_BBBBBBBBBBB"

    When I send a GET request to "/api/v2/tickets/{t1}/messages/{m2}/attachments"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements
    And the JSON node "data[0].blob.blob_auth" should contain "m2blob_CCCCCCCCCCC"
