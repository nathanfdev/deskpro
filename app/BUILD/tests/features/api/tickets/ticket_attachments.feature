@new
Feature: /tickets/{id}/attachments endpoint
  To LIST DeskPRO ticket attachments
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |
    And only the following TicketMessage records exist:
      | #  | Ticket | Message | Is Agent Note |
      | m1 | {t1}   | Message | 0             |
      | m2 | {t1}   | Note    | 1             |

  Scenario: I retrieve a ticket attachments empty list
    When I send a GET request to "/api/v2/tickets/{t1}/attachments"
    Then the response status code should be 200
    And the JSON node "data" should have 0 element

  Scenario: I retrieve a ticket attachments
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    And I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    And only the following TicketAttachment records exist:
      | Ticket | Person  | Message | Blob                      |
      | {t1}   | {admin} | {m1}    | {blob_AAAAAAAAAAAAAAAAAA} |
      | {t1}   | {admin} | {m1}    | {blob_BBBBBBBBBBBBBBBBBB} |
    When I send a GET request to "/api/v2/tickets/{t1}/attachments"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].blob.blob_auth" should contain "AAAAAAAAAAAAAAAAAA"
    And the JSON node "data[1].blob.blob_auth" should contain "BBBBBBBBBBBBBBBBBB"

  Scenario: I retrieve a ticket attachments list by ticket ref
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    And only the following TicketAttachment records exist:
      | Ticket | Person  | Message | Blob                      |
      | {t1}   | {admin} | {m1}    | {blob_AAAAAAAAAAAAAAAAAA} |
    When I send a GET request to "/api/v2/tickets/{t1:ref}/attachments"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements
    And the JSON node "data[0].blob.blob_auth" should contain "AAAAAAAAAAAAAAAAAA"
